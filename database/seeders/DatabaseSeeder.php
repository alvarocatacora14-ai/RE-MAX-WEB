<?php

namespace Database\Seeders;

use App\Models\ConfiguracionContacto;
use App\Models\Propiedad;
use App\Models\Vendedor;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Configuración única de contacto
        ConfiguracionContacto::obtenerUnica();

        // 2. Los 3 vendedores iniciales del padrón propio
        $vendedoresIniciales = [
            [
                'nombres' => 'Jessica',
                'apellidos' => 'Sardon',
                'celular' => '969 322 977',
                'foto' => 'vendedores/actual/Top1.jpeg',
                'activo' => true,
            ],
            [
                'nombres' => 'Vanessa',
                'apellidos' => 'Ramirez',
                'celular' => '958 953 697',
                'foto' => 'vendedores/actual/Top2.jpeg',
                'activo' => true,
            ],
            [
                'nombres' => 'Julissa',
                'apellidos' => 'Valdivia',
                'celular' => '934 712 169',
                'foto' => 'vendedores/actual/Top3.jpeg',
                'activo' => true,
            ],
        ];

        foreach ($vendedoresIniciales as $datosVendedor) {
            Vendedor::firstOrCreate(
                [
                    'nombres' => $datosVendedor['nombres'],
                    'apellidos' => $datosVendedor['apellidos'],
                ],
                $datosVendedor
            );
        }

        // 3. Las 7 propiedades iniciales (sin precios ni monedas inventadas: precio = null, moneda = null)
        $propiedadesIniciales = [
            [
                'nombre' => 'Proyecto Zeus',
                'ubicacion' => 'Cayma, Arequipa',
                'enlace_remax' => 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-cerro-colorado-arequipa-arequipa-1153660/',
                'imagen' => 'propiedades/Zeus1.jpg',
                'precio' => null,
                'moneda' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Proyecto Costa Dorada',
                'ubicacion' => 'Islay, Arequipa',
                'enlace_remax' => 'https://www.remax.pe/web/search/property/propiedad-terreno-en-venta-mollendo-islay-arequipa-1156893/',
                'imagen' => 'propiedades/Costa1.jpg',
                'precio' => null,
                'moneda' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Edificio Samay',
                'ubicacion' => 'Cayma, Arequipa',
                'enlace_remax' => 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-cayma-arequipa-arequipa-1168464/',
                'imagen' => 'propiedades/EdSamay1.jpg',
                'precio' => null,
                'moneda' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Proyecto Gardenia',
                'ubicacion' => 'Cayma, Arequipa',
                'enlace_remax' => 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-cayma-arequipa-arequipa-1164684/',
                'imagen' => 'propiedades/Gardenia1.jpg',
                'precio' => null,
                'moneda' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Proyecto Chacarilla',
                'ubicacion' => 'San Borja, Lima',
                'enlace_remax' => 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-san-borja-lima-lima-1149713/',
                'imagen' => 'propiedades/ProyectoChacarilla1.jpg',
                'precio' => null,
                'moneda' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Departamento Av. Pardo',
                'ubicacion' => 'Miraflores, Lima',
                'enlace_remax' => 'https://www.remax.pe/web/search/property/propiedad-departamento-en-venta-miraflores-lima-lima-1163921/',
                'imagen' => 'propiedades/DptoLima1.jpg',
                'precio' => null,
                'moneda' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Casa Urb. Garibaldi',
                'ubicacion' => 'Ilo, Moquegua',
                'enlace_remax' => 'https://www.remax.pe/web/search/property/propiedad-casa-en-venta-ilo-ilo-moquegua-1165067/',
                'imagen' => 'propiedades/CasaIlo.jpg',
                'precio' => null,
                'moneda' => null,
                'activo' => true,
            ],
        ];

        foreach ($propiedadesIniciales as $datosPropiedad) {
            Propiedad::firstOrCreate(
                ['nombre' => $datosPropiedad['nombre']],
                $datosPropiedad
            );
        }
    }
}
