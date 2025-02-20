@extends('admin.users.form', [
    'user' => $user,
    'roles' => \Spatie\Permission\Models\Role::all()
])
