@extends('layouts.app')

@section('title','Editar Producto')

@push('css')
<style>
    #descripcion {
        resize: none;
    }
    .required:after {
        content: " *";
        color: red;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Producto</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('productos.index')}}">Productos</a></li>
        <li class="breadcrumb-item active">Editar producto</li>
    </ol>

    <div class="card">
        <form action="{{route('productos.update',['producto'=>$producto])}}" method="post" enctype="multipart/form-data">
            @method('PATCH')
            @csrf
            <div class="card-header bg-primary text-white">
                <i class="fas fa-box me-2"></i>
                <strong>Formulario de Edición</strong>
            </div>
            <div class="card-body">

                {{-- Información Básica --}}
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-info-circle me-2"></i>Información Básica
                    </h5>
                </div>

                <div class="row g-4">
                    <!----Codigo---->
                    <div class="col-md-6">
                        <label for="codigo" class="form-label required">Código:</label>
                        <input type="text"
                               name="codigo"
                               id="codigo"
                               class="form-control @error('codigo') is-invalid @enderror"
                               value="{{old('codigo',$producto->codigo)}}"
                               placeholder="Ej: PROD-001">
                        @error('codigo')
                        <div class="invalid-feedback">{{$message}}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-barcode"></i> Código único del producto
                        </small>
                    </div>

                    <!---Nombre---->
                    <div class="col-md-6">
                        <label for="nombre" class="form-label required">Nombre:</label>
                        <input type="text"
                               name="nombre"
                               id="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{old('nombre',$producto->nombre)}}"
                               placeholder="Ej: Coca Cola">
                        @error('nombre')
                        <div class="invalid-feedback">{{$message}}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-tag"></i> Nombre descriptivo del producto
                        </small>
                    </div>

                    <!---Descripción---->
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion"
                                  id="descripcion"
                                  rows="3"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="Descripción detallada del producto (opcional)">{{old('descripcion',$producto->descripcion)}}</textarea>
                        @error('descripcion')
                        <div class="invalid-feedback">{{$message}}</div>
                        @enderror
                    </div>
                </div>

                {{-- Clasificación --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-layer-group me-2"></i>Clasificación
                    </h5>
                </div>

                <div class="row g-4">
                    <!---Marca---->
                    <div class="col-md-6">
                        <label for="marca_id" class="form-label required">Marca:</label>
                        <select data-size="5"
                                title="Seleccione una marca"
                                data-live-search="true"
                                name="marca_id"
                                id="marca_id"
                                class="form-control selectpicker show-tick @error('marca_id') is-invalid @enderror">
                            @foreach ($marcas as $item)
                            @if ($producto->marca_id == $item->id)
                            <option selected value="{{$item->id}}" {{ old('marca_id') == $item->id ? 'selected' : '' }}>
                                {{$item->nombre}}
                            </option>
                            @else
                            <option value="{{$item->id}}" {{ old('marca_id') == $item->id ? 'selected' : '' }}>
                                {{$item->nombre}}
                            </option>
                            @endif
                            @endforeach
                        </select>
                        @error('marca_id')
                        <div class="invalid-feedback d-block">{{$message}}</div>
                        @enderror
                    </div>

                    <!---Presentaciones---->
                    <div class="col-md-6">
                        <label for="presentacione_id" class="form-label required">Presentación:</label>
                        <select data-size="5"
                                title="Seleccione una presentación"
                                data-live-search="true"
                                name="presentacione_id"
                                id="presentacione_id"
                                class="form-control selectpicker show-tick @error('presentacione_id') is-invalid @enderror">
                            @foreach ($presentaciones as $item)
                            @if ($producto->presentacione_id == $item->id)
                            <option selected value="{{$item->id}}" {{ old('presentacione_id') == $item->id ? 'selected' : '' }}>
                                {{$item->nombre}}
                            </option>
                            @else
                            <option value="{{$item->id}}" {{ old('presentacione_id') == $item->id ? 'selected' : '' }}>
                                {{$item->nombre}}
                            </option>
                            @endif
                            @endforeach
                        </select>
                        @error('presentacione_id')
                        <div class="invalid-feedback d-block">{{$message}}</div>
                        @enderror
                    </div>

                    <!---Unidad de Medida---->
                    <div class="col-md-6">
                        <label for="unidad_medida_id" class="form-label">Unidad de Medida:</label>
                        <select data-size="5"
                                title="Seleccione una unidad"
                                data-live-search="true"
                                name="unidad_medida_id"
                                id="unidad_medida_id"
                                class="form-control selectpicker show-tick @error('unidad_medida_id') is-invalid @enderror">
                            <option value="">Sin unidad de medida</option>
                            @foreach ($unidadesMedida->groupBy('tipo') as $tipo => $unidades)
                                <optgroup label="{{ ucfirst($tipo) }}">
                                    @foreach ($unidades as $item)
                                    <option value="{{$item->id}}"
                                            {{ old('unidad_medida_id', $producto->unidad_medida_id) == $item->id ? 'selected' : '' }}>
                                        {{$item->nombre}} ({{$item->abreviatura}})
                                    </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('unidad_medida_id')
                        <div class="invalid-feedback d-block">{{$message}}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-balance-scale"></i> Unidad en la que se mide/vende (opcional)
                        </small>
                    </div>

                    <!---Categorías---->
                    <div class="col-md-6">
                        <label for="categorias" class="form-label required">Categorías:</label>
                        <select data-size="5"
                                title="Seleccione las categorías"
                                data-live-search="true"
                                name="categorias[]"
                                id="categorias"
                                class="form-control selectpicker show-tick @error('categorias') is-invalid @enderror"
                                multiple>
                            @foreach ($categorias as $item)
                            @if (in_array($item->id,$producto->categorias->pluck('id')->toArray()))
                            <option selected value="{{$item->id}}" {{ (in_array($item->id , old('categorias',[]))) ? 'selected' : '' }}>
                                {{$item->nombre}}
                            </option>
                            @else
                            <option value="{{$item->id}}" {{ (in_array($item->id , old('categorias',[]))) ? 'selected' : '' }}>
                                {{$item->nombre}}
                            </option>
                            @endif
                            @endforeach
                        </select>
                        @error('categorias')
                        <div class="invalid-feedback d-block">{{$message}}</div>
                        @enderror
                    </div>
                </div>

                {{-- Información Adicional --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-info-circle me-2"></i>Información Adicional
                    </h5>
                </div>

                <div class="row g-4">
                    <!---Fecha de vencimiento---->
                    <div class="col-md-6">
                        <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento:</label>
                        <input type="date"
                               name="fecha_vencimiento"
                               id="fecha_vencimiento"
                               class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                               value="{{old('fecha_vencimiento',$producto->fecha_vencimiento)}}">
                        @error('fecha_vencimiento')
                        <div class="invalid-feedback">{{$message}}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-calendar-alt"></i> Solo para productos perecederos (opcional)
                        </small>
                    </div>

                    <!---Imagen---->
                    <div class="col-md-6">
                        <label for="img_path" class="form-label">Imagen:</label>
                        <input type="file"
                               name="img_path"
                               id="img_path"
                               class="form-control @error('img_path') is-invalid @enderror"
                               accept="image/*">
                        @error('img_path')
                        <div class="invalid-feedback">{{$message}}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-image"></i> Imagen del producto (opcional, máx. 2MB)
                        </small>
                        @if($producto->img_path)
                        <div class="mt-2">
                            <small class="text-muted">Imagen actual:</small><br>
                            <img src="{{ Storage::url('public/productos/'.$producto->img_path) }}"
                                 alt="{{$producto->nombre}}"
                                 class="img-thumbnail"
                                 style="max-width: 200px;">
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            <div class="card-footer text-center">
                <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
@endpush
