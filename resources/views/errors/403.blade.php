@extends('errors.layout')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __('Restricted Sector. You do not have the required clearance to access these administrative coordinates.'))
