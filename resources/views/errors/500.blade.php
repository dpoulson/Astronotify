@extends('errors.layout')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Cosmic Collision. An unexpected malfunction occurred in our server telemetry. We are looking into the system logs.'))
