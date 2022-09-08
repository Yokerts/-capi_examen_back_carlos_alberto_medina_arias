<?php

namespace App\Http\Controllers\EXPORT;

use App\Http\Controllers\EXPORT\LIBS\GenerateFILE;
use App\Http\Controllers\EXPORT\LIBS\GenerateORDENVENTA;

class UtilPDFController extends AuthPDFController
{
    public static function GenerarFILE($settings, $ventas, $title = "Titulo del documento", $nombre_archivo = "nombre-del-documento", $tipo_archivo = "PDF", $accion = "VER", $tamanio = "CV", $usuario_exporto = "Marck Desing", $proyecto = "", $sucursal_exporto = "", $toEmail = false)
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

            'proyecto' => $proyecto,

            'folio' => $settings['FOLIO'] ?? "",
            'fecha_alta' => $settings['FECHA_ALTA'] ?? "",
            'fecha_entrega' => $settings['FECHA_ENTREGA'] ?? "",

            'title' => $title,
            'usuario_exporto' => $usuario_exporto,
            'sucursal_exporto' => $sucursal_exporto,

            'SETTINGS' => array(
                'fileName' => $nombre_archivo,
                'toEmail' => $toEmail,
                'type' => $tipo_archivo,
                'size' => $size,
                'action' => $accion
            ),

            'INFO' => array(

                'CONFIGURACION' => array(
                    'COLOR_PRIMARIO' => $settings['COLOR'][0] ?? '#fb6819',
                    'COLOR_SECUNDARIO' => $settings['COLOR'][1] ?? '#464A4A',
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

            'CONFIGURAR_DATOS_ETIQUETAS_CABECERAS' => array(
                'SHOW_TITLE' => $settings['CABECERA']['SHOW_TITLE'] ?? false,
                'SHOW_DATOS' => $settings['CABECERA']['SHOW_DATOS'] ?? false,
                'TITLE' => $settings['CABECERA']['TITLE'],
                'SHOW_HIDDEN_COLUMNA_CABECERA' => $settings['CABECERA']['SHOW_HIDDEN'] ?? "",
                'DATOS' => $settings['CABECERA']['DATOS'] ?? array('Texto 1' => "E1", 'Texto 2' => "E2")
            ),

            'CONFIGURAR_DATOS_ETIQUETAS_DETALLES' => array(
                'SHOW_TITLE' => true,

                'SHOW_TITLE_TABLE' => true,
                'SHOW_DATOS_TABLE' => true,

                'SHOW_TITLE_SUB_TABLE' => true,
                'SHOW_DATOS_SUB_TABLE' => true,

                'TITLE' => $settings['DETALLE']['TITLE'],
                'TAMANIO_CELDA_DETALLE' => $settings['DETALLE']['TAMANIO'] ?? '',
                'ALINEACION_CELDA_DETELLE' => $settings['DETALLE']['ALINEACION'] ?? '',
                'SHOW_HIDDEN_COLUMNA_DETALLE' => $settings['DETALLE']['SHOW_HIDDEN'] ?? "",
                'SHOW_HIDDEN_COLUMNA_DETALLE_NODO' => "",

                'HTML_ETIQUETA' => $settings['DETALLE']['HTML_ETIQUETA'] ?? array(),

                'HTML_CAMPOS' => $settings['DETALLE']['HTML_CAMPOS'] ?? array(),

                'HTML_ETIQUETA_NODO' => $settings['DETALLE']['HTML_ETIQUETA_NODO'] ?? array(),
                'HTML_CAMPOS_NODO' => $settings['DETALLE']['HTML_CAMPOS_NODO'] ?? array(),

                'HTML_DATOS' => $ventas
            ),

            'CONFIGURAR_DATOS_ETIQUETAS_DETALLES_SUB_TABLA' => array(
                'TAMANIO_CELDA_DETALLE_SUB_TABLA' => $settings['DETALLE']['TAMANIO_CELDA_DETALLE_SUB_TABLA'] ?? '',
                'ALINEACION_CELDA_DETALLE_SUB_TABLA' => $settings['DETALLE']['ALINEACION_CELDA_DETALLE_SUB_TABLA'] ?? '',
            )
        );


        return GenerateFILE::GeneratePDF($DATO, $DATOSEXCEL);

    }

    public static function GenerarTICKET($settings, $tamanio = null)
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
                    'COLOR_PRIMARIO' => $settings['COLOR'][0] ?? '#fb6819',
                    'COLOR_SECUNDARIO' => $settings['COLOR'][1] ?? '#464A4A',
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

}
