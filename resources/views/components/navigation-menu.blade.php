<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <div class="sb-sidenav-menu-heading">Inicio</div>
                <a class="nav-link" href="{{ route('panel') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Panel
                </a>

                <!---div class="sb-sidenav-menu-heading">Interface</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    Layouts
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="layout-static.html">Static Navigation</a>
                        <a class="nav-link" href="layout-sidenav-light.html">Light Sidenav</a>
                    </nav>
                </div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                    <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                    Pages
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseAuth" aria-expanded="false" aria-controls="pagesCollapseAuth">
                            Authentication
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="pagesCollapseAuth" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="login.html">Login</a>
                                <a class="nav-link" href="register.html">Register</a>
                                <a class="nav-link" href="password.html">Forgot Password</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                            Error
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="401.html">401 Page</a>
                                <a class="nav-link" href="404.html">404 Page</a>
                                <a class="nav-link" href="500.html">500 Page</a>
                            </nav>
                        </div>
                    </nav>
                </div--->

                <div class="sb-sidenav-menu-heading">Modulos</div>

                  <!----Ventas---->
                @can('ver-venta')
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseVentas" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    Ventas
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseVentas" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        @can('ver-venta')
                        <a class="nav-link" href="{{ route('ventas.index') }}">Ver</a>
                        @endcan
                        @can('crear-venta')
                        <a class="nav-link" href="{{ route('ventas.create') }}">Crear</a>
                        @endcan
                    </nav>
                </div>
                @endcan

                  <!----Cotizaciones---->
                @can('ver-cotizacion')
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCotizacion" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                    Cotizaciones
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCotizacion" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        @can('ver-cotizacion')
                        <a class="nav-link" href="{{ route('cotizaciones.index') }}">Ver</a>
                        @endcan
                        @can('crear-cotizacion')
                        <a class="nav-link" href="{{ route('cotizaciones.create') }}">Crear</a>
                        @endcan
                    </nav>
                </div>
                @endcan

                <!----Compras---->
                @can('ver-compra')
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCompras" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-store"></i></div>
                    Compras
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCompras" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        @can('ver-compra')
                        <a class="nav-link" href="{{ route('compras.index') }}">Ver</a>
                        @endcan
                        @can('crear-compra')
                        <a class="nav-link" href="{{ route('compras.create') }}">Crear</a>
                        @endcan
                    </nav>
                </div>
                @endcan

                <!----Parametros---->
                {{-- @can('ver-categoria' || 'ver-presentacione' || 'ver-marca' || 'ver-ubicacion' || 'ver-unidad-medida') --}}
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseParametros" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-pen"></i></i></div>
                    Parametros
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseParametros" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        @can('ver-categoria')
                        <a class="nav-link" href="{{ route('categorias.index') }}">Categorias</a>
                        @endcan
                        @can('ver-presentacione')
                        <a class="nav-link" href="{{ route('presentaciones.create') }}">Presentaciones</a>
                        @endcan

                        @can('ver-marca')
                        <a class="nav-link" href="{{ route('marcas.create') }}">Marcas</a>
                        @endcan
                        @can('ver-ubicacion')
                        <a class="nav-link" href="{{ route('ubicaciones.create') }}">Ubicaciones</a>
                        @endcan
                        @can('ver-unidad-medida')
                        <a class="nav-link" href="{{ route('unidades-medida.index') }}">Unidades de medida</a>
                        @endcan
                    </nav>
                </div>
                {{-- @endcan --}}



                {{-- @can('ver-categoria')
                <a class="nav-link" href="{{ route('categorias.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-tag"></i></div>
                    Categorías
                </a>
                @endcan

                @can('ver-presentacione')
                <a class="nav-link" href="{{ route('presentaciones.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-box-archive"></i></div>
                    Presentaciones
                </a>
                @endcan

                @can('ver-marca')
                <a class="nav-link" href="{{ route('marcas.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-bullhorn"></i></div>
                    Marcas
                </a>
                @endcan --}}
                {{-- //!Productos --}}
                 @can('ver-producto')
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProductos" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fa-brands fa-shopify"></i></div>
                    Productos
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseProductos" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        @can('ver-producto')
                        <a class="nav-link" href="{{ route('productos.index') }}">Ver productos</a>
                        @endcan
                        @can('ver-producto-danado')
                        <a class="nav-link" href="{{ route('productos-danados.index') }}">Productos dañados</a>
                        @endcan
                        {{-- @can('ver-alerta-stock')
                        <a class="nav-link" href="{{ route('alertas-stock.index') }}">Alertas</a>
                        @endcan --}}
                    </nav>
                </div>
                @endcan

                @can('ver-inventario')
                <li class="nav-item">
                    {{-- <a class="nav-link {{ request()->routeIs('inventario-sucursal.*') ? 'active' : '' }}" --}}
                    <a class="nav-link"
                    href="{{ route('inventario-sucursal.index') }}">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        Inventario por Sucursal
                    </a>
                </li>
                @endcan


                @can('ver-cliente')
                <a class="nav-link" href="{{ route('clientes.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                    Clientes
                </a>
                @endcan



                @can('ver-proveedore')
                <a class="nav-link" href="{{ route('proveedores.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user-group"></i></div>
                    Proveedores
                </a>
                @endcan

                {{-- @can('ver-ubicacion') --}}
                {{-- <li class="nav-item"> --}}
                    {{-- <a class="nav-link {{ request()->routeIs('ubicaciones.*') ? 'active' : '' }}" --}}
                    {{-- <a class="nav-link " --}}
                    {{-- href="{{ route('ubicaciones.index') }}"> --}}
                        {{-- <div class="sb-nav-link-icon"> --}}
                            {{-- <i class="fas fa-map-marker-alt"></i> --}}
                        {{-- </div> --}}
                        {{-- Ubicaciones --}}
                    {{-- </a> --}}
                {{-- </li> --}}
                {{-- @endcan --}}
                {{-- @can('ver-unidad-medida') --}}
                {{-- <li class="nav-item"> --}}
                    {{-- <a class="nav-link {{ request()->routeIs('unidades-medida.*') ? 'active' : '' }}" --}}
                    {{-- <a class="nav-link "
                    href="{{ route('unidades-medida.index') }}">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        Unidades de Medida
                    </a>
                </li>
                @endcan --}}

                @hasrole('administrador')
                <div class="sb-sidenav-menu-heading">OTROS</div>
                @endhasrole

                @can('ver-reportes')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reportes.index') }}">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reportes</span>
                        </a>
                    </li>
                    @endcan

                @can('ver-user')
                <a class="nav-link" href="{{ route('users.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                    Usuarios
                </a>
                @endcan

                @can('ver-role')
                <a class="nav-link" href="{{ route('roles.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-person-circle-plus"></i></div>
                    Roles
                </a>
                @endcan


                {{-- //*Administración de sucursales --}}
                 @can('ver-sucursal')
                <a class="nav-link" href="{{ route('sucursales.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-building-user"></i></div>
                    Sucursales
                </a>
                @endcan


            </div>
        </div>
      <div class="sb-sidenav-footer">
    <div class="small">Bienvenido:</div>
    @if(auth()->check())
        {{ auth()->user()->name }}
    @else
        Invitado
    @endif
</div>
    </nav>
</div>
