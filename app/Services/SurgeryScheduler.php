<?php

namespace App\Services;

use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurgeryScheduler
{
    /**
     * Verifica si es posible programar una cirugía en la fecha y hora especificadas
     */
    public function validateScheduling(array $data): bool
    {
        $surgeryDate = Carbon::parse($data['surgery_date']);
        
        // Verificar el tiempo mínimo de anticipación
        $minNotice = config('surgery.scheduling.min_notice');
        if ($surgeryDate->diffInHours(now()) < $minNotice) {
            throw ValidationException::withMessages([
                'surgery_date' => ["La cirugía debe programarse con al menos {$minNotice} horas de anticipación."]
            ]);
        }

        // Verificar que la fecha no exceda el límite máximo
        $maxFutureDays = config('surgery.scheduling.max_future_days');
        if ($surgeryDate->diffInDays(now()) > $maxFutureDays) {
            throw ValidationException::withMessages([
                'surgery_date' => ["No se pueden programar cirugías con más de {$maxFutureDays} días de anticipación."]
            ]);
        }

        // Verificar horario laboral
        $workingHours = config('surgery.scheduling.working_hours');
        $startTime = Carbon::parse($workingHours['start']);
        $endTime = Carbon::parse($workingHours['end']);
        
        if ($surgeryDate->format('H:i') < $startTime->format('H:i') || 
            $surgeryDate->format('H:i') > $endTime->format('H:i')) {
            throw ValidationException::withMessages([
                'surgery_date' => ['La hora debe estar dentro del horario laboral.']
            ]);
        }

        return true;
    }

    /**
     * Verifica la disponibilidad del equipo para una fecha específica
     */
    public function checkEquipmentAvailability(Collection $equipment, Carbon $date): array
    {
        $unavailableEquipment = [];

        foreach ($equipment as $item) {
            // Verificar si el equipo está en mantenimiento
            if ($item->status === 'maintenance') {
                $unavailableEquipment[] = [
                    'equipment' => $item,
                    'reason' => 'En mantenimiento'
                ];
                continue;
            }

            // Verificar si el equipo está asignado a otra cirugía en la misma fecha
            $conflictingSurgery = Surgery::whereHas('equipment', function ($query) use ($item) {
                $query->where('equipment.id', $item->id);
            })
            ->where('surgery_date', $date->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->first();

            if ($conflictingSurgery) {
                $unavailableEquipment[] = [
                    'equipment' => $item,
                    'reason' => 'Asignado a otra cirugía',
                    'surgery' => $conflictingSurgery
                ];
            }
        }

        return $unavailableEquipment;
    }

    /**
     * Verifica la disponibilidad del personal para una fecha específica
     */
    public function checkStaffAvailability(Collection $staff, Carbon $date): array
    {
        $unavailableStaff = [];

        foreach ($staff as $member) {
            // Verificar si el miembro del personal está asignado a otra cirugía en la misma fecha
            $conflictingSurgery = Surgery::whereHas('staff', function ($query) use ($member) {
                $query->where('users.id', $member->id);
            })
            ->where('surgery_date', $date->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->first();

            if ($conflictingSurgery) {
                $unavailableStaff[] = [
                    'staff' => $member,
                    'reason' => 'Asignado a otra cirugía',
                    'surgery' => $conflictingSurgery
                ];
            }
        }

        return $unavailableStaff;
    }

    /**
     * Programa una nueva cirugía
     */
    public function schedule(array $data): Surgery
    {
        return DB::transaction(function () use ($data) {
            // Validar la programación
            $this->validateScheduling($data);

            // Verificar disponibilidad de equipo
            $equipment = Equipment::findMany($data['equipment']);
            $unavailableEquipment = $this->checkEquipmentAvailability(
                $equipment, 
                Carbon::parse($data['surgery_date'])
            );

            if (!empty($unavailableEquipment)) {
                throw ValidationException::withMessages([
                    'equipment' => ['Hay equipo no disponible para la fecha seleccionada.']
                ]);
            }

            // Verificar disponibilidad de personal
            $staff = User::findMany($data['staff']);
            $unavailableStaff = $this->checkStaffAvailability(
                $staff,
                Carbon::parse($data['surgery_date'])
            );

            if (!empty($unavailableStaff)) {
                throw ValidationException::withMessages([
                    'staff' => ['Hay personal no disponible para la fecha seleccionada.']
                ]);
            }

            // Crear la cirugía
            $surgery = Surgery::create($data);

            // Asignar equipo y personal
            $surgery->equipment()->attach($data['equipment']);
            $surgery->staff()->attach($data['staff']);

            // Actualizar estado del equipo
            Equipment::whereIn('id', $data['equipment'])
                ->update(['status' => 'in_use']);

            return $surgery;
        });
    }

    /**
     * Reprograma una cirugía existente
     */
    public function reschedule(Surgery $surgery, array $data): Surgery
    {
        return DB::transaction(function () use ($surgery, $data) {
            // Validar la nueva programación
            $this->validateScheduling($data);

            // Liberar equipo actual
            Equipment::whereIn('id', $surgery->equipment->pluck('id'))
                ->update(['status' => 'available']);

            // Verificar disponibilidad de nuevo equipo
            $equipment = Equipment::findMany($data['equipment']);
            $unavailableEquipment = $this->checkEquipmentAvailability(
                $equipment,
                Carbon::parse($data['surgery_date'])
            );

            if (!empty($unavailableEquipment)) {
                throw ValidationException::withMessages([
                    'equipment' => ['Hay equipo no disponible para la fecha seleccionada.']
                ]);
            }

            // Verificar disponibilidad de nuevo personal
            $staff = User::findMany($data['staff']);
            $unavailableStaff = $this->checkStaffAvailability(
                $staff,
                Carbon::parse($data['surgery_date'])
            );

            if (!empty($unavailableStaff)) {
                throw ValidationException::withMessages([
                    'staff' => ['Hay personal no disponible para la fecha seleccionada.']
                ]);
            }

            // Actualizar la cirugía
            $surgery->update($data);

            // Actualizar equipo y personal
            $surgery->equipment()->sync($data['equipment']);
            $surgery->staff()->sync($data['staff']);

            // Actualizar estado del nuevo equipo
            Equipment::whereIn('id', $data['equipment'])
                ->update(['status' => 'in_use']);

            return $surgery;
        });
    }
}
