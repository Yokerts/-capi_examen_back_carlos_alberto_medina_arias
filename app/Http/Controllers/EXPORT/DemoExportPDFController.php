<?php

namespace App\Http\Controllers\EXPORT;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Excel\ExcelExport;
use App\Http\Controllers\EXPORT\LIBS\GenerateFILE;
use Maatwebsite\Excel\Facades\Excel;

class DemoExportPDFController extends Controller
{
    public function pdfexcel($int_type = null, $int_accion = null, $int_size = null)
    {

        $proyecto = "Test Project";
        $title = "Titulo del documento";
        $fileName = "nombre-del-documento";
        $usuario_exporto = "Usuario Paterno Materno";
        $sucursal_exporto = "Matriz";

        $toEmail = false;

        switch ($int_type) {
            case 1:
                $type = 'PDF';
                break;
            case 2:
                $type = 'EXCEL';
                break;
            default:
                $type = 'PDF';
        }

        switch ($int_accion) {
            case 1:
                $action = 'VER';
                break;
            case 2:
                $action = 'IMPRIMIR';
                break;
            case 3:
                $action = 'DESCARGAR';
                break;
            default:
                $action = 'VER';
        }

        switch ($int_size) {
            case 1:
                $size = 'CartaVertical';
                break;
            case 2:
                $size = 'CartaHorizontal';
                break;
            case 3:
                $size = 'OficioVertical';
                break;
            case 4:
                $size = 'OficioHorizontal';
                break;
            default:
                $size = 'CartaVertical';
        }

        // Hidden-Cabecera-Detalle
        // Hidden-Cabecera
        // Hidden-Cabecera-Detalle-Datos
        // Hidden-Cabecera-Datos

        $DATO = array(

            'folio' => '',
            'fecha_alta' => '',
            'fecha_entrega' => '',

            'proyecto' => $proyecto,

            'title' => $title,
            'usuario_exporto' => $usuario_exporto,
            'sucursal_exporto' => $sucursal_exporto,

            'SETTINGS' => array(
                'fileName' => $fileName,
                'toEmail' => $toEmail,
                'type' => $type,
                'size' => $size,
                'action' => $action
            ),

            'INFO' => array(

                'CONFIGURACION' => array(
                    'COLOR_PRIMARIO' => '#fb6819',
                    'COLOR_SECUNDARIO' => '#464A4A',
                    'MARCA_DE_AGUA' => 'Fondo.png',
                    'POSICION_LOGO' => 'LEFT',
                    'POSICION_CELDA_DETALLE' => '0,1,2',
                    'POSICION_CELDA_DETALLE_NODO' => '0,1,2,3,4',
                    'TEXTO_EXTRA' => array(
                        array('TEXTO' => 'Texto extra 1'),
                        array('TEXTO' => 'Texto extra 2'),
                        array('TEXTO' => 'Texto extra 3'),
                        array('TEXTO' => 'Texto extra 4'),
                    )
                ),

                'EMPRESA_RAZON_SOCIAL' => 'Empresa S. A.',
                'EMPRESA_DIRECCION' => '8 poniente Norte # 1018, Niño de atocha',
                'EMPRESA_TELEFONO' => '0000000000',
                'EMPRESA_LOGO' => 'Logo.png',
                'EMPRESA_NOMBRE_COMERCIAL' => 'Empresa soluciones S. A.',
                'EMPRESA_SUCURSAL' => 'Tuxtla Gutierrez',
                'EMPRESA_CLAVE' => 'CV030491ODRL',
                'EMPRESA_RFC' => 'RULO910304HOZPS6',
                'EMPRESA_PAGINA_WEB' => 'http://empresa.com',
                'EMPRESA_CORREO_ELECTRONICO' => 'correo@empresa.com.mx'

            ),

            'CONFIGURAR_DATOS_ETIQUETAS_CABECERAS' => array(
                'SHOW_TITLE' => true,
                'SHOW_DATOS' => true,
                'TITLE' => 'Datos Cabeceras',
                'SHOW_HIDDEN_COLUMNA_CABECERA' => "SHOW,SHOW,SHOW,SHOW,SHOW,SHOW,SHOW",
                'DATOS' => array(
                    'Nombre completo' => "Nombre Paterno Materno",
                    'Domicilio' => "8 poniente Norte # 1018, Niño de atocha",
                    'Correo Electrónico' => "correo@empresa.com.mx",
                    'Teléfono' => "0000000000",
                    'Celular' => "0000000000",
                    'RFC' => "RULO910304HOZPS6",
                    'CURL' => "RULO910304HOZPS6QWE123ASD",
                )
            ),

            'CONFIGURAR_DATOS_ETIQUETAS_DETALLES' => array(
                'SHOW_TITLE' => true,

                'SHOW_TITLE_TABLE' => true,
                'SHOW_DATOS_TABLE' => true,

                'SHOW_TITLE_SUB_TABLE' => true,
                'SHOW_DATOS_SUB_TABLE' => true,

                'TITLE' => 'Datos Detalles',
                'ALINEACION_CELDA_DETELLE' => 'L,C,R',
                'TAMANIO_CELDA_DETALLE' => '',
                'SHOW_HIDDEN_COLUMNA_DETALLE' => "SHOW,SHOW,SHOW",
                'SHOW_HIDDEN_COLUMNA_DETALLE_NODO' => "SHOW,SHOW,SHOW,SHOW,SHOW",

                'HTML_ETIQUETA' => array(
                    '0' => "Nombre",
                    '1' => "Apellido",
                    '2' => "Edad"
                ),
                'HTML_CAMPOS' => array(
                    '0' => array('NOMBRE' => 'NOMBRE'),
                    '1' => array('NOMBRE' => 'APELLIDO'),
                    '2' => array('NOMBRE' => 'EDAD')
                ),

                'HTML_ETIQUETA_NODO' => array(
                    '0' => "ID",
                    '1' => "Texto",
                    '2' => "Status",
                    '3' => "Edad",
                    '4' => "Sexo"
                ),
                'HTML_CAMPOS_NODO' => array(
                    '0' => array('NOMBRE' => 'ID'),
                    '1' => array('NOMBRE' => 'TEXTO'),
                    '2' => array('NOMBRE' => 'STATUS'),
                    '3' => array('NOMBRE' => 'EDAD'),
                    '4' => array('NOMBRE' => 'SEXO')
                ),

                'HTML_DATOS' => array(
                    '0' => array(
                        'NOMBRE' => "Oswaldo",
                        'APELLIDO' => "Ruiz Lopez",
                        'EDAD' => "34 Años",
                        'SUB_NODO_PDF_EXPORT' => array(
                            array(
                                'ID' => "# 1",
                                'TEXTO' => "# Leer el cuestionario",
                                'STATUS' => "# Activo",
                                'EDAD' => "# 27",
                                'SEXO' => "# Hombre",
                            ),
                            array(
                                'ID' => "# 1",
                                'TEXTO' => "# Leer el cuestionario",
                                'STATUS' => "# Activo",
                                'EDAD' => "# 27",
                                'SEXO' => "# Hombre",
                            )
                        )
                    ),
                    '1' => array(
                        'NOMBRE' => "Yoel",
                        'APELLIDO' => "Ruiz Lopez",
                        'EDAD' => "34 Años",
                        'SUB_NODO_PDF_EXPORT' => array(
                            array(
                                'ID' => "# 1",
                                'TEXTO' => "# Leer el cuestionario",
                                'STATUS' => "# Activo",
                                'EDAD' => "# 27",
                                'SEXO' => "# Hombre",
                            ),
                            array(
                                'ID' => "# 1",
                                'TEXTO' => "# Leer el cuestionario",
                                'STATUS' => "# Activo",
                                'EDAD' => "# 27",
                                'SEXO' => "# Hombre",
                            )
                        )
                    ),
                    '2' => array(
                        'NOMBRE' => "Rosa",
                        'APELLIDO' => "Ruiz Lopez",
                        'EDAD' => "34 Años",
                        'SUB_NODO_PDF_EXPORT' => array()
                    ),
                    '3' => array(
                        'NOMBRE' => "Rosa",
                        'APELLIDO' => "Ruiz Lopez",
                        'EDAD' => "34 Años",
                        'SUB_NODO_PDF_EXPORT' => array()
                    ),
                    '4' => array(
                        'NOMBRE' => "Rosa",
                        'APELLIDO' => "Ruiz Lopez",
                        'EDAD' => "34 Años",
                        'SUB_NODO_PDF_EXPORT' => array()
                    ),
                    '5' => array(
                        'NOMBRE' => "Rosa",
                        'APELLIDO' => "Ruiz Lopez",
                        'EDAD' => "34 Años",
                        'SUB_NODO_PDF_EXPORT' => array()
                    ),
                    '6' => array(
                        'NOMBRE' => "Rosa",
                        'APELLIDO' => "Ruiz Lopez",
                        'EDAD' => "34 Años",
                        'SUB_NODO_PDF_EXPORT' => array()
                    ),
                    '7' => array(
                        'NOMBRE' => "Rosa",
                        'APELLIDO' => "Ruiz Lopez",
                        'EDAD' => "34 Años",
                        'SUB_NODO_PDF_EXPORT' => array()
                    )
                )
            )
        );


        GenerateFILE::GeneratePDF($DATO, $DATOSEXCEL);
    }

    public function ticket($tamanio = null)
    {
        switch ($tamanio) {
            case "CV":
                $size = 'CartaVertical';
                break;
            case "CH":
                $size = 'CartaHorizontal';
                break;
            case "OV":
                $size = 'OficioVertical';
                break;
            case "OH":
                $size = 'OficioHorizontal';
                break;
            default:
                $size = 'CartaVertical';
        }

        $TEXTO_EXTRA = array();

        $DATO = array(
            'SETTINGS' => array(
                'action' => "VER",
                'fileName' => "Ticket"
            ),
            'INFO' => array(

                'CONFIGURACION' => array(
                    'COLOR_PRIMARIO' => '#fb6819',
                    'COLOR_SECUNDARIO' => '#464A4A',
                    'MARCA_DE_AGUA' => 'Fondo.png',
                    'POSICION_LOGO' => 'LEFT',
                    'POSICION_CELDA_DETALLE' => '',
                    'POSICION_CELDA_DETALLE_NODO' => '',
                    'TEXTO_EXTRA' => $TEXTO_EXTRA
                ),

                'EMPRESA_NOMBRE_COMERCIAL' => env('PDF_EMPRESA_NOMBRE_COMERCIAL'),
                'EMPRESA_RAZON_SOCIAL' => env('PDF_EMPRESA_RAZON_SOCIAL'),
                'EMPRESA_DIRECCION' => env('PDF_EMPRESA_DIRECCION'),
                'EMPRESA_TELEFONO' => env('PDF_EMPRESA_TELEFONO'),
                'EMPRESA_LOGO' => env('PDF_EMPRESA_LOGO'),
                'EMPRESA_SUCURSAL' => env('PDF_EMPRESA_SUCURSAL'),
                'EMPRESA_CLAVE' => env('PDF_EMPRESA_CLAVE'),
                'EMPRESA_RFC' => env('PDF_EMPRESA_RFC'),
                'EMPRESA_PAGINA_WEB' => env('PDF_EMPRESA_PAGINA_WEB'),
                'EMPRESA_CORREO_ELECTRONICO' => env('PDF_EMPRESA_CORREO_ELECTRONICO')

            ),
            'VENTA' => array(
                'CABECERA' => array(
                    array(
                        'REGFECHA' => date("Y-m-d"),
                        'CLTNOMBRECOMERCIAL' => "Oswaldo",
                        'FORMAPAGO' => "Efectivo",
                        'METODOPAGO' => "En una sola exhibición",
                        'ALMACEN' => "Tuxtla",
                    )
                ),
                'DETALLE' => array(
                    array(
                        'PRODESCRIPCION' => "Lona impresa",
                        'VTDCANTIDAD' => "1",
                        'VTDPRECIO$' => "2",
                        'VTDDESCUENTO$' => "2",
                        'VTDCOSTO$' => "3",
                    ),
                    array(
                        'PRODESCRIPCION' => "Playeras",
                        'VTDCANTIDAD' => "1",
                        'VTDPRECIO$' => "2",
                        'VTDDESCUENTO$' => "2",
                        'VTDCOSTO$' => "3",
                    ),
                    array(
                        'PRODESCRIPCION' => "Tazas",
                        'VTDCANTIDAD' => "1",
                        'VTDPRECIO$' => "2",
                        'VTDDESCUENTO$' => "2",
                        'VTDCOSTO$' => "3",
                    )
                )
            )
        );
        GenerateFILE::GenerateTICKET($DATO);
    }

    public function excel()
    {
        $header = [
            'ID',
            'Mombre'
        ];

        $body = array(
            array(
                'id' => 1,
                'name' => 'Oswaldo'
            ),
            array(
                'id' => 2,
                'name' => 'Ruiz'
            ),
        );

        $body = collect($body);

        $excel = new ExcelExport($header, $body);
        return Excel::download($excel, 'users.xlsx');
    }
}
