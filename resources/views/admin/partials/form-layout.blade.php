@extends('adminlte::page')

@section('title', $title ?? 'Panel Administrativo')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>{{ $header ?? $title ?? 'Panel Administrativo' }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Inicio</a></li>
                @isset($breadcrumb)
                    @foreach($breadcrumb as $item)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            @if(!$loop->last && isset($item['url']))
                                <a href="{{ $item['url'] }}">{{ $item['text'] }}</a>
                            @else
                                {{ $item['text'] }}
                            @endif
                        </li>
                    @endforeach
                @endisset
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $cardTitle ?? $title ?? 'Formulario' }}</h3>
                    @isset($cardTools)
                        <div class="card-tools">
                            {!! $cardTools !!}
                        </div>
                    @endisset
                </div>

                <form method="{{ $method ?? 'POST' }}" action="{{ $action }}" enctype="multipart/form-data" id="{{ $formId ?? 'mainForm' }}">
                    @csrf
                    @if(($method ?? 'POST') == 'PUT' || ($method ?? 'POST') == 'PATCH')
                        @method($method)
                    @endif

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{ $slot }}
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ $submitText ?? 'Guardar' }}
                        </button>

                        <a href="{{ $cancelRoute ?? url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>

                        @isset($additionalButtons)
                            {!! $additionalButtons !!}
                        @endisset
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    @stack('css')
@stop

@section('js')
    <script>
        $(function() {
            // Inicializar Select2 en todos los select del formulario
            $('.select2').select2({
                theme: 'bootstrap4',
                language: 'es'
            });

            // Inicializar DateTimePicker en los campos de fecha
            $('.datetimepicker').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss',
                locale: 'es',
                icons: {
                    time: 'fas fa-clock',
                    date: 'fas fa-calendar',
                    up: 'fas fa-chevron-up',
                    down: 'fas fa-chevron-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'fas fa-calendar-check',
                    clear: 'fas fa-trash',
                    close: 'fas fa-times'
                }
            });

            // Inicializar DatePicker en los campos de solo fecha
            $('.datepicker').datetimepicker({
                format: 'YYYY-MM-DD',
                locale: 'es',
                icons: {
                    time: 'fas fa-clock',
                    date: 'fas fa-calendar',
                    up: 'fas fa-chevron-up',
                    down: 'fas fa-chevron-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'fas fa-calendar-check',
                    clear: 'fas fa-trash',
                    close: 'fas fa-times'
                }
            });

            // Confirmar antes de enviar el formulario
            $('#{{ $formId ?? 'mainForm' }}').on('submit', function(e) {
                @if($confirmSubmit ?? false)
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Confirma que deseas guardar los cambios",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
                @endif
            });
        });
    </script>
    @stack('js')
@stop
