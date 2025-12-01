@extends('layouts.app')

@section('title','Editar cliente')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Cliente</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('clientes.index')}}">Clientes</a></li>
        <li class="breadcrumb-item active">Editar cliente</li>
    </ol>

    <div class="card text-bg-light">
        <form action="{{ route('clientes.update',['cliente'=>$cliente]) }}" method="post">
            @method('PATCH')
            @csrf
            <div class="card-header">
                <p>Tipo de cliente: <span class="fw-bold">{{ strtoupper($cliente->persona->tipo_persona)}}</span></p>
            </div>
            <div class="card-body">

                <div class="row g-3">

                    <!----NIT (Obligatorio)----->
                    <div class="col-md-6">
                        <label for="nit" class="form-label">NIT: <span class="text-danger">*</span></label>
                        <input required type="text" name="nit" id="nit" class="form-control" value="{{old('nit', $cliente->persona->nit)}}" placeholder="Ejemplo: 12345678-9">
                        @error('nit')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!-------Razón social------->
                    <div class="col-md-6">
                        @if ($cliente->persona->tipo_persona == 'natural')
                        <label id="label-natural" for="razon_social" class="form-label">Nombres y apellidos: <span class="text-danger">*</span></label>
                        @else
                        <label id="label-juridica" for="razon_social" class="form-label">Nombre de la empresa: <span class="text-danger">*</span></label>
                        @endif

                        <input required type="text" name="razon_social" id="razon_social" class="form-control" value="{{old('razon_social',$cliente->persona->razon_social)}}">

                        @error('razon_social')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!------Nombre comercial (opcional)---->
                    <div class="col-md-6">
                        <label for="nombre_comercial" class="form-label">Nombre comercial: <small class="text-muted">(Opcional)</small></label>
                        <input type="text" name="nombre_comercial" id="nombre_comercial" class="form-control" value="{{old('nombre_comercial', $cliente->persona->nombre_comercial)}}">
                        @error('nombre_comercial')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!------Dirección---->
                    <div class="col-md-6">
                        <label for="direccion" class="form-label">Dirección: <span class="text-danger">*</span></label>
                        <input required type="text" name="direccion" id="direccion" class="form-control" value="{{old('direccion',$cliente->persona->direccion)}}">
                        @error('direccion')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!------Email (opcional)---->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email: <small class="text-muted">(Opcional)</small></label>
                        <input type="email" name="email" id="email" class="form-control" value="{{old('email', $cliente->persona->email)}}" placeholder="ejemplo@correo.com">
                        @error('email')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!------Teléfono (opcional)---->
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono: <small class="text-muted">(Opcional)</small></label>
                        <input type="text" name="telefono" id="telefono" class="form-control" value="{{old('telefono', $cliente->persona->telefono)}}" placeholder="5555-5555">
                        @error('telefono')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!------Separador visual---->
                    <div class="col-12">
                        <hr>
                        <h6 class="text-muted">Documento adicional (Opcional)</h6>
                        <small class="text-muted">Si el cliente cuenta con DPI, pasaporte, licencia u otro documento, puede registrarlo aquí.</small>
                    </div>

                    <!--------------Documento (Opcional)------->
                    <div class="col-md-6">
                        <label for="documento_id" class="form-label">Tipo de documento: <small class="text-muted">(Opcional)</small></label>
                        <select class="form-select" name="documento_id" id="documento_id">
                            <option value="">Seleccione una opción</option>
                            @foreach ($documentos as $item)
                            @if ($cliente->persona->documento_id == $item->id)
                            <option selected value="{{$item->id}}" {{ old('documento_id') == $item->id ? 'selected' : '' }}>{{$item->tipo_documento}}</option>
                            @else
                            <option value="{{$item->id}}" {{ old('documento_id') == $item->id ? 'selected' : '' }}>{{$item->tipo_documento}}</option>
                            @endif
                            @endforeach
                        </select>
                        @error('documento_id')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="numero_documento" class="form-label">Número de documento: <small class="text-muted">(Opcional)</small></label>
                        <input type="text" name="numero_documento" id="numero_documento" class="form-control" value="{{old('numero_documento',$cliente->persona->numero_documento)}}">
                        @error('numero_documento')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                </div>

            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')

@endpush
