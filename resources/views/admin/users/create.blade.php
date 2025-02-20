@extends('admin.users.form', [
    'roles' => \Spatie\Permission\Models\Role::all()
])
