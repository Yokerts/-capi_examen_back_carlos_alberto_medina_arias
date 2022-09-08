<?php

namespace App\Http\Controllers\EXPORT\LIBS;

/**
 * Pulgada – 2.54 cm
 * Punto – 1/72 pulgada
 * Punto – 0.35 mm – 0.03527 7777 (valor tomado en los calculos)
 *
 * Por defecto es mm
 * Ejemplo Tamaño Carta medidas w 21.59 | h 27.94.
 * 612 x 72 = 44064
 * 792 x 72 = 57024
 * 21.59 / 612 = 0.03527 7777
 * 27.94 / 792 = 0.03527 7777
 * 21.59 / 0.03527 7777 = 612.0000135
 * 27.94 / 0.03527 7777 = 792.0000175
 *
 * Tomando el valor en mm las medidas aumentan un poco, queda a criterio de cada uno
 *
 * 21.59 / 0.035 = 616.86
 * 27.94 / 0.035 = 798.29
 *
 * Media Carta
 * 10.79 / 0.03527 7777 = 311 – 310.9606368
 * 13.97 / 0.03527 7777 = 396
 *
 * $pdf->fwPt = 616.86;
 * $pdf->fhPt = 798.29;
 */
class GenerateFILE
{

    public static function GeneratePDF($dato, &$DATOSEXCEL)
    {
        $HEX01 = $dato['INFO']['CONFIGURACION']['COLOR_PRIMARIO'];

        $HEX02 = $dato['INFO']['CONFIGURACION']['COLOR_SECUNDARIO'];

        if ($HEX02 == "" || $HEX01 == "") {
            $dato['INFO']['CONFIGURACION']['COLOR_PRIMARIO'] = '#E4100A';
            $dato['INFO']['CONFIGURACION']['COLOR_SECUNDARIO'] = '#25A79F';
        }

        $_CONFIGCELDA['TAMANIO_CELDA_DETALLE_SUB_TABLA'] = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES_SUB_TABLA']['TAMANIO_CELDA_DETALLE_SUB_TABLA'];
        $_CONFIGCELDA['ALINEACION_CELDA_DETALLE_SUB_TABLA'] = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES_SUB_TABLA']['ALINEACION_CELDA_DETALLE_SUB_TABLA'];

        $_CONFIGCELDA['TAMANIO_CELDA_DETALLE'] = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['TAMANIO_CELDA_DETALLE'];
        $_CONFIGCELDA['ALINEACION_CELDA_DETELLE'] = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['ALINEACION_CELDA_DETELLE'];
        $_CONFIGCELDA['SHOW_HIDDEN_COLUMNA'] = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['SHOW_HIDDEN_COLUMNA_DETALLE'];
        $_CONFIGCELDA['SHOW_HIDDEN_COLUMNA_NODO'] = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['SHOW_HIDDEN_COLUMNA_DETALLE_NODO'];

        $_CONFIGCELDA['SHOW_HIDDEN_COLUMNA_CABECERA'] = $dato['CONFIGURAR_DATOS_ETIQUETAS_CABECERAS']['SHOW_HIDDEN_COLUMNA_CABECERA'];

        $_CONFIGCELDA['MARCA_DE_AGUA'] = $dato['INFO']['CONFIGURACION']['MARCA_DE_AGUA'];
        if ($dato['INFO']['CONFIGURACION']['POSICION_LOGO']) {
            $_CONFIGCELDA['POSICION_LOGO'] = $dato['INFO']['CONFIGURACION']['POSICION_LOGO'];
        } else {
            $_CONFIGCELDA['POSICION_LOGO'] = "RIGHT";
        }
        $_CONFIGCELDA['POSICION_CELDA_DETALLE'] = $dato['INFO']['CONFIGURACION']['POSICION_CELDA_DETALLE'];

        $_CONFIGCELDA['POSICION_CELDA_DETALLE_NODO'] = $dato['INFO']['CONFIGURACION']['POSICION_CELDA_DETALLE_NODO'];

        $_CONFIGCELDA['TEXTO_EXTRA'] = $dato['INFO']['CONFIGURACION']['TEXTO_EXTRA'];

        if ($dato['CONFIGURAR_DATOS_ETIQUETAS_CABECERAS']['SHOW_TITLE'] || $dato['CONFIGURAR_DATOS_ETIQUETAS_CABECERAS']['SHOW_DATOS']) {

            $TIPO_FORMS = "CABECERADTALLE";

            /*
             * FUNCIONALIDAD CABECERA
             * */

            $HTML_ETIQUETA_CABECERA = $dato['CONFIGURAR_DATOS_ETIQUETAS_CABECERAS']['DATOS'];
            $CD_Print = "Vacio";

            foreach ($HTML_ETIQUETA_CABECERA as $key => $val) {
                if ($val != "" && $val != NULL) {
                    $CD_Print = "ConDatos";
                }
            }

            /*
             * FUNCIONALIDAD DETALLE
             * */

            $HTML_ETIQUETA_DETALLE = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_ETIQUETA'];
            $HTML_CAMPOS_DETALLE = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_CAMPOS'];

            $HTML_ETIQUETA_DETALLE_NODO = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_ETIQUETA_NODO'];
            $HTML_CAMPOS_NODO = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_CAMPOS_NODO'];

            $HTML_DATOS_DETALLE = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_DATOS'];

        } else {

            $TIPO_FORMS = "DETALLE";

            /*
             * FUNCIONALIDAD CABECERA
             * */

            $HTML_ETIQUETA_CABECERA = array();
            $CD_Print = "Vacio";

            /*
             * FUNCIONALIDAD DETALLE
             * */

            $HTML_ETIQUETA_DETALLE = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_ETIQUETA'];
            $HTML_CAMPOS_DETALLE = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_CAMPOS'];

            $HTML_ETIQUETA_DETALLE_NODO = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_ETIQUETA_NODO'];
            $HTML_CAMPOS_NODO = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_CAMPOS_NODO'];

            $HTML_DATOS_DETALLE = $dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['HTML_DATOS'];
        }

        switch ($dato['SETTINGS']['size']) {
            case 'CartaVertical':
                $pdf = new PDF('P', 'mm', 'Letter');
                $CANPUNTO = 150;
                break;
            case 'CartaHorizontal':
                $pdf = new PDF('L', 'mm', 'Letter');
                $CANPUNTO = 150;
                break;
            case 'OficioVertical':
                $pdf = new PDF('P', 'mm', 'legal');
                $CANPUNTO = 150;
                break;
            case 'OficioHorizontal':
                $pdf = new PDF('L', 'mm', 'legal');
                $CANPUNTO = 150;
                break;
        }

        $LINEAPUNTEADA = '';

        for ($PUN = 0; $PUN < $CANPUNTO; $PUN++) {
            $LINEAPUNTEADA .= " - ";
        }

        $LINEAPUNTEADA = trim($LINEAPUNTEADA);

        $pdf->setDATOS($dato);
        $pdf->setReferenceWatermark(1);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false, 20);

        $INFOFOOTER[0] = $pdf->DecodeText($dato['INFO']['EMPRESA_RAZON_SOCIAL']);
        $INFOFOOTER[1] = $pdf->DecodeText($dato['INFO']['EMPRESA_DIRECCION']) . " | " . $pdf->DecodeText($dato['INFO']['EMPRESA_TELEFONO']) . " | " . $pdf->DecodeText($dato['INFO']['EMPRESA_PAGINA_WEB']);

        // INICIO LOGO
        switch ($_CONFIGCELDA['POSICION_LOGO']) {
            case 'RIGHT':
                switch ($dato['SETTINGS']['size']) {
                    case 'CartaVertical':
                        $cw = 161.5;
                        break;
                    case 'CartaHorizontal':
                        $cw = 226;
                        break;
                    case 'OficioVertical':
                        $cw = 161.5;
                        break;
                    case 'OficioHorizontal':
                        $cw = 301.5;
                        break;
                }

                $ch = 12;

                $wi = 40;
                $hi = 25;
                break;
            case 'LEFT':
                $cw = 12;
                $ch = 12;

                $wi = 40;
                $hi = 25;
                break;
        }

        if ($dato['INFO']['EMPRESA_LOGO'] != "") {
            $rootlogo = env('DOC_IMAGE_TO_PDF') . $dato['INFO']['EMPRESA_LOGO'];
            if (file_exists($rootlogo)) {
                $pdf->Image($rootlogo, $cw, $ch, $wi, $hi);
            }
        }

        $CONTINUACION_Y1 = ($ch + $hi);
        // FIN LOGO


        // INICIO VARIABLES
        $x = 12;
        $y = 12;
        $texX = 90;
        $texY = 4;

        $pdf->SetXY($x, $y);
        // FIN VARIABLES

        // INICIO DATOS DERECHA
        switch ($_CONFIGCELDA['POSICION_LOGO']) {
            case 'RIGHT':
                $x = $x;
                break;
            case 'LEFT':
                $x = $x + 42;
                break;
        }

        if ($dato['INFO']['EMPRESA_NOMBRE_COMERCIAL']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x, $y);
            $pdf->printColor('negro', 10, '#ffffff');
            $pdf->MultiCell($texX, $texY, $pdf->DecodeText($dato['INFO']['EMPRESA_NOMBRE_COMERCIAL']), 0, 'L', false);
        }

        if ($dato['INFO']['EMPRESA_RAZON_SOCIAL']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x, $y);
            $pdf->printColor('negro', 9, '#ffffff');
            $pdf->MultiCell($texX, $texY, $pdf->DecodeText($dato['INFO']['EMPRESA_RAZON_SOCIAL']), 0, 'L', false);
        }

        if ($dato['INFO']['EMPRESA_SUCURSAL']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x, $y);
            $pdf->printColor('negroregular', 8, '#ffffff');
            $pdf->MultiCell($texX, $texY, $pdf->DecodeText($dato['INFO']['EMPRESA_SUCURSAL']), 0, 'L', false);
        }

        if ($dato['INFO']['EMPRESA_CLAVE']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x, $y);
            $pdf->printColor('negroregular', 8, '#ffffff');
            $pdf->MultiCell($texX, $texY, $pdf->DecodeText($dato['INFO']['EMPRESA_CLAVE']), 0, 'L', false);
        }

        if ($dato['INFO']['EMPRESA_RFC']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x, $y);
            $pdf->printColor('negroregular', 8, '#ffffff');
            $pdf->MultiCell($texX, $texY, $pdf->DecodeText($dato['INFO']['EMPRESA_RFC']), 0, 'L', false);
        }

        if ($dato['INFO']['EMPRESA_DIRECCION']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x, $y);
            $pdf->printColor('negroregular', 8, '#ffffff');
            $pdf->MultiCell($texX, $texY, $pdf->DecodeText($dato['INFO']['EMPRESA_DIRECCION']), 0, 'L', false);
        }

        if ($dato['INFO']['EMPRESA_TELEFONO'] && $dato['INFO']['EMPRESA_CORREO_ELECTRONICO']) {

            if ($dato['INFO']['EMPRESA_TELEFONO']) {
                $y = $pdf->GetY();
                $pdf->SetXY($x, $y);
                $pdf->printColor('negroregular', 8, '#ffffff');
                $pdf->MultiCell($texX, $texY, $pdf->DecodeText("Tel.: " . $dato['INFO']['EMPRESA_TELEFONO'] . "   Email: " . $dato['INFO']['EMPRESA_CORREO_ELECTRONICO']), 0, 'L', false);
            }

        } else {

            if ($dato['INFO']['EMPRESA_TELEFONO']) {
                $y = $pdf->GetY();
                $pdf->SetXY($x, $y);
                $pdf->printColor('negroregular', 8, '#ffffff');
                $pdf->MultiCell($texX, $texY, $pdf->DecodeText("Tel.: " . $dato['INFO']['EMPRESA_TELEFONO']), 0, 'L', false);
            }

            if ($dato['INFO']['EMPRESA_CORREO_ELECTRONICO']) {
                $y = $pdf->GetY();
                $pdf->SetXY($x, $y);
                $pdf->printColor('negroregular', 8, '#ffffff');
                $pdf->MultiCell($texX, $texY, $pdf->DecodeText("Email: " . $dato['INFO']['EMPRESA_CORREO_ELECTRONICO']), 0, 'L', false);
            }

        }

        $CONTINUACION_Y2 = $pdf->GetY();
        // FIN DATOS DERECHA

        // INICIO VARIABLES
        $x = 5;
        $y = 12;
        switch ($dato['SETTINGS']['size']) {
            case 'CartaVertical':
                $texX = 97.8;
                break;
            case 'CartaHorizontal':
                $texX = 155;
                break;
            case 'OficioVertical':
                $texX = 230;
                $texX = 97.8;
                break;
            case 'OficioHorizontal':
                $texX = 238;
                break;
        }
        $texY = 5;
        $pdf->SetXY($x, $y);
        // FIN VARIABLES

        // INICIO DATOS IZQUIERDA
        switch ($_CONFIGCELDA['POSICION_LOGO']) {
            case 'RIGHT':
                $x = $x;
                break;
            case 'LEFT':
                $x = $x + 42;
                break;
        }

        $y = $pdf->GetY();
        $pdf->SetXY($x + 97, $y);
        $pdf->printColor('negro', 13, '#ffffff');
        $pdf->MultiCell($texX - 40, $texY, utf8_decode($dato['title']), 0, 'R', false);

        if ($dato['folio']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 97, $y);
            $pdf->printColor(null, 13, '#FFFFFF', '#ff0000');
            $pdf->MultiCell($texX - 40, $texY, "# " . utf8_decode($dato['folio']), 0, 'R', true);
        }

        if ($dato['fecha_alta']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 97, $y);
            $pdf->printColor('negroregular', 10, '#ffffff');
            $pdf->MultiCell($texX - 40, $texY, utf8_decode("Fecha alta: " . $dato['fecha_alta']), 0, 'R', true);
        }

        if ($dato['fecha_entrega']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 97, $y);
            $pdf->printColor('negroregular', 10, '#ffffff');
            $pdf->MultiCell($texX - 40, $texY, utf8_decode("Fecha entrega: " . $dato['fecha_entrega']), 0, 'R', true);
        }

        $y = $pdf->GetY();
        $pdf->SetXY($x + 97, $y);
        $pdf->printColor('', 8, '#FFFFFF', '#808080');
        $pdf->MultiCell($texX - 40, $texY, utf8_decode("Fecha de exportación: " . date("d-m-Y")), 0, 'R', false);

        if ($dato['usuario_exporto']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 97, $y);
            $pdf->printColor('', 8, '#FFFFFF', '#808080');
            $pdf->MultiCell($texX - 40, $texY, utf8_decode("Usuario: " . $dato['usuario_exporto']), 0, 'R', false);
        }

        if ($dato['sucursal_exporto']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 97, $y);
            $pdf->printColor('', 8, '#FFFFFF', '#808080');
            $pdf->MultiCell($texX - 40, $texY, utf8_decode("Sucursal: " . $dato['sucursal_exporto']), 0, 'R', false);
        }

        if ($dato['proyecto']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 97, $y);
            $pdf->printColor('', 8, '#FFFFFF', '#808080');
            $pdf->MultiCell($texX - 40, $texY, utf8_decode("Proyecto: " . $dato['proyecto']), 0, 'R', false);
        }

        $CONTINUACION_Y3 = $pdf->GetY();

        // FIN DATOS IZQUIERDA

        // INICIO TABLA PRINCIPAL
        switch ($dato['SETTINGS']['size']) {
            case 'CartaVertical':
                $TPapelH = 191.6;
                $TPapelV = 245;
                $lineFin = $TPapelH;
                break;
            case 'CartaHorizontal':
                $TPapelH = 256;
                $TPapelV = 185;
                $lineFin = $TPapelH;
                break;
            case 'OficioVertical':
                $TPapelH = 191.6;
                $TPapelV = 325;
                $lineFin = $TPapelH;
                break;
            case 'OficioHorizontal':
                $TPapelH = 331.6;
                $TPapelV = 185;
                $lineFin = $TPapelH;
                break;
        }

        $x = 12;
        $y = max($CONTINUACION_Y1, $CONTINUACION_Y2, $CONTINUACION_Y3) + 3;
        $pdf->SetY($y);

        // CABECERA INICIO
        $y = $pdf->GetY();
        if ($TIPO_FORMS == "CABECERADTALLE" && $CD_Print == "ConDatos") {

            if ($dato['CONFIGURAR_DATOS_ETIQUETAS_CABECERAS']['SHOW_TITLE'] === true) {
                $pdf->SetXY($x, $y);
                $pdf->printColor('rojo', 10, $HEX01);
                $pdf->CellFitSpace($TPapelH, 5, utf8_decode($dato['CONFIGURAR_DATOS_ETIQUETAS_CABECERAS']['TITLE'] ?? 'C A B E C E R A'), "", 0, 'C', true);
                $y += $texY;
            }

            if ($dato['CONFIGURAR_DATOS_ETIQUETAS_CABECERAS']['SHOW_DATOS'] === true) {
                $pdf->SetXY($x, $y);
                $y = $pdf->GetY();
                $pdf->printColor('gris', 8, '#e5e5e5');
                $pdf->CabeceraDetalle($_CONFIGCELDA, $HTML_ETIQUETA_CABECERA, $TPapelH, $y, $_EXCELCABECERADETALLE);
                $y = $pdf->GetY();
                $pdf->SetY($y + 5);
            }

        }

        if (!$DATOSEXCEL) {
            $DATOSEXCEL = array();
        }

        if (!isset($_EXCELCABECERADETALLE)) {
            $_EXCELCABECERADETALLE = array();
        }


        $DATOSEXCEL['_EXCELCABECERADETALLE'] = $_EXCELCABECERADETALLE;
        // CABECERA FIN

        // DTALLE INICIO

        if ($dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['SHOW_TITLE'] === true) {
            $y = $pdf->GetY();
            $pdf->SetXY($x, $y);
            $pdf->printColor('rojo', 10, $HEX01);
            $pdf->CellFitSpace($TPapelH, 5, utf8_decode($dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['TITLE'] ?? "D E T A L L E S"), "", 0, 'C', true);
        }

        $y = $pdf->GetY() + 5;

        $pdf->SetXY($x, $y);

        $w = $TPapelH / count($HTML_ETIQUETA_DETALLE);

        if ($dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['SHOW_TITLE_TABLE'] === true) {
            $pdf->printColor('gris', 8, $HEX02);
            $pdf->DetallesHeadTable($HTML_ETIQUETA_DETALLE, $w, 5, $x, $y, true, $TPapelH, $_CONFIGCELDA, $_EXCELHEADERDETALLE);
            $DATOSEXCEL['EXCEL_HEADER_DETALLE'] = $_EXCELHEADERDETALLE;
            $y = $pdf->GetY() + 5;
        }


        if ($dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['SHOW_DATOS_TABLE'] === true) {
            $pdf->DetallesBodyTable($dato, $HTML_CAMPOS_DETALLE, $HTML_CAMPOS_NODO, $HTML_ETIQUETA_DETALLE_NODO, $TPapelH, $HTML_DATOS_DETALLE, $w, 5, $x, $y, $lineFin, $TPapelV, $_CONFIGCELDA, $_EXCELBODYDETALLE);
            $DATOSEXCEL['EXCEL_BODY_DETALLE'] = $_EXCELBODYDETALLE;
            $y = $pdf->GetY();
        }
        // DTALLE FIN

        // FIN TABLA PRINCIPAL

        // INICIO WATERMARCK
        $pdf->printWATERMARCK($_CONFIGCELDA, $y);
        // FIN WATERMARCK

        // INICIO ETIQUETA FOOTER
        $y = -15;
        $x = 12;
        $pdf->SetY($y);
        $pdf->SetX($x);
        $pdf->SetFillColor(255, 255, 255);//Fondo gris
        $pdf->SetTextColor(10, 140, 223); //Letra color azul
        $pdf->SetFont('Arial', '', 8);
        $pdf->CellFitSpace($TPapelH, 3, $LINEAPUNTEADA . $LINEAPUNTEADA, 0, 0, 'C', true);
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0); //Letra color azul
        $pdf->SetFont('Arial', '', 7);
        $pdf->CellFitSpace($TPapelH, 3, $INFOFOOTER[0], 0, 0, 'C', true);
        $pdf->Ln();
        $pdf->CellFitSpace($TPapelH, 3, $INFOFOOTER[1], 0, 0, 'C', true);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 4);
        $pdf->CellFitSpace($TPapelH, 3, utf8_decode(" O R I G I N A L"), 0, 0, 'R', true);

        // FIN ETIQUETA FOOTER
        // echo "<pre>";
        // print_r($DATOSEXCEL);
        // echo "</pre>";
        // exit();

        switch ($dato['SETTINGS']['type']) {
            case 'PDF':

                if ($dato['SETTINGS']['toEmail']) {

                    $root = env('DOC_TEMP_TO_PDF');

                    if (!is_dir($root)) {
                        mkdir($root, 0777);
                    }

                    $path = $root . ($dato['SETTINGS']['fileName'] ?? "file") . "-" . rand(10000, 99999) . rand(10000, 99999) . "-" . strtotime(date('Y-m-d H:i:s')) . ".pdf";

                    $pdf->Output($path, "F");

                    if (file_exists($path)) {
                        $result = array(
                            'success' => true,
                            'status' => 200,
                            'message' => 'Archivo creado con éxito.',
                            'data' => array(
                                "path" => $path,
                            )
                        );
                    } else {
                        $result = array(
                            'success' => false,
                            'status' => 400,
                            'message' => 'Error al crear el archivo.',
                            'data' => array()
                        );
                    }

                    return $result;

                } else {

                    switch ($dato['SETTINGS']['action']) {
                        case 'DESCARGAR':

                            $filename = $dato['SETTINGS']['fileName'] . "-" . date("d-m-Y His") . ".pdf";
                            $pdf->Output('D', utf8_decode($filename));

                            break;
                        case 'VER':

                            $filename = $dato['SETTINGS']['fileName'] . "-" . date("d-m-Y His") . ".pdf";
                            $pdf->Output('I', utf8_decode($filename));

                            break;
                        case 'IMPRIMIR':

                            $filename = $dato['SETTINGS']['fileName'] . "-" . date("d-m-Y His") . ".pdf";
                            $pdf->AutoPrint(true);
                            $pdf->Output('I', utf8_decode($filename));

                            break;
                    }

                }

                break;
            case 'EXCEL':

                dd('En Desarrollo, Exportacion en excel');

                break;
        }
    }

    public static function GenerateTICKET($dato, $TAM_H_mm = 85, $TAM_W_mm = 58)
    {

        foreach ($dato['VENTA']['DETALLE'] as $key => $row) {
            $cantidadchar = strlen($row['PRODESCRIPCION']);
            $cantidadchar = number_format($cantidadchar / 30);
            if ($cantidadchar == 0) {
                $cantidadchar = 1;
            }
            $TAM_H_mm = $TAM_H_mm + ($cantidadchar * 2.5);
        }

//        define("FONT_TICKET_B", "courier");
//        define("FONT_TICKET_R", "courier");

        define("FONT_TICKET_B", "helvetica");
        define("FONT_TICKET_R", "helvetica");

//        define("FONT_TICKET_B", "times");
//        define("FONT_TICKET_R", "times");

//        define("FONT_TICKET_B", "AGENCYB");
//        define("FONT_TICKET_R", "AGENCYR");

//        define("FONT_TICKET_B", "Arial");
//        define("FONT_TICKET_R", "Arial");
        $SIZEFONTDETALLE = 5;
        $LIMITACION = 0;
        $TPapelTiket = array($TAM_W_mm, $TAM_H_mm);

        $pdf = new PDF('P', 'mm', $TPapelTiket);
        $pdf->AddPage();


        #Establecemos los márgenes izquierda, arriba y derecha:
        $pdf->SetMargins(2, 3, 2);
        #Establecemos el margen inferior:
        $pdf->SetAutoPageBreak(true, 3);


        // INICIO LOGO
        $cw = 2;
        $ch = 3;
        $wi = 15;
        $hi = 9;
        if ($dato['INFO']['EMPRESA_LOGO'] != "") {
            if (file_exists(env('DOC_IMAGE_TO_PDF') . $dato['INFO']['EMPRESA_LOGO'])) {
                $pdf->Image(env('DOC_IMAGE_TO_PDF') . $dato['INFO']['EMPRESA_LOGO'], $cw, $ch, $wi, $hi);
            }
        }
        // FIN LOGO

        // INICIO VARIABLES
        $x = 2;
        $y = 3;
        $texX = 54;
        $texY = 2.2;
        $pdf->SetXY($x, $y);
        // FIN VARIABLES

        // INICIO DATOS DERECHA

        if ($dato['INFO']['EMPRESA_RAZON_SOCIAL']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 15, $y);
            $pdf->printColorTicket('negro', 7, '#ffffff');
            $pdf->MultiCell($texX - 15, $texY + 1, $pdf->DecodeText($dato['INFO']['EMPRESA_RAZON_SOCIAL']), $LIMITACION, 'L', false);
        }

        // $pdf->Cell(0,2, "Your Name", '1', 2, 'L', false); //Name

        if ($dato['INFO']['EMPRESA_SUCURSAL']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 15, $y);
            $pdf->printColorTicket('negro', 6, '#ffffff');
            $pdf->MultiCell($texX - 15, $texY + 1, $pdf->DecodeText($dato['INFO']['EMPRESA_SUCURSAL']), $LIMITACION, 'L', false);
        }

        if ($dato['INFO']['EMPRESA_RFC']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 15, $y);
            $pdf->printColorTicket('negro', 5, '#ffffff');
            $pdf->MultiCell($texX - 15, $texY + 1, $pdf->DecodeText($dato['INFO']['EMPRESA_RFC']), $LIMITACION, 2, 'L', false);
        }

        if ($dato['INFO']['EMPRESA_DIRECCION']) {
            $y = $pdf->GetY();
            $pdf->SetXY($x + 10, $y);
            $pdf->printColorTicket('negro', 5, '#ffffff');
            $pdf->MultiCell($texX - 15, $texY, $pdf->DecodeText($dato['INFO']['EMPRESA_DIRECCION']), $LIMITACION, 'C', false);
            $pdf->Ln();
        }

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *", $LIMITACION, 'C', false);

        // FIN DATOS DERECHA

        // INICIO DATOS CABECERA
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "Fecha de venta: " . $pdf->DecodeText($dato['VENTA']['CABECERA'][0]['REGFECHA']), $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "Clt: " . $pdf->DecodeText($dato['VENTA']['CABECERA'][0]['CLTNOMBRECOMERCIAL']), $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "Forma de pago: " . $pdf->DecodeText($dato['VENTA']['CABECERA'][0]['FORMAPAGO']), $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "Metodo de pago: " . $pdf->DecodeText($dato['VENTA']['CABECERA'][0]['METODOPAGO']), $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "Almacen: " . $pdf->DecodeText($dato['VENTA']['CABECERA'][0]['ALMACEN']), $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -", $LIMITACION, 'C', false);

        // FIN DATOS CABECERA
        // INICIO HEADER TABLE TICKET
        $filasCabeceras = array(
            'Producto',
            'Cantidad',
            'P. unitario',
            'Descuento',
            'Importe'
        );

        $tamanio = array(16, 8, 11, 9, 10);
        $aling = array('L', 'R', 'R', 'R', 'R');


        $pdf->styleRect($LIMITACION); /*margin de celdas*/
        $pdf->BorderTable($LIMITACION); /*para activar color de la fila*/

        $pdf->SetWidths($tamanio); /*tamaño de celdas array*/
        $pdf->SetAligns($aling); /*alineacion de texto en celda array*/

        $pdf->SetFont(FONT_TICKET_R, '', $SIZEFONTDETALLE - 1);

        $pdf->Row($filasCabeceras);
        // FIN HEADER TABLE TICKET

//        // INICIO HEADER TABLE TICKET
        foreach ($dato['VENTA']['DETALLE'] as $key => $row) {
            if ($row['PRODESCRIPCION'] != NULL) {
                $GION = "-";
            } else {
                $GION = "";
            }
            $filasCabeceras = array(
                $GION . utf8_decode($row['PRODESCRIPCION']),
                $row['VTDCANTIDAD'],
                $row['VTDPRECIO$'],
                $row['VTDDESCUENTO$'],
                $row['VTDCOSTO$']
            );
            $TOTALGENERAL = $row['VTDCOSTO$'];
            $aling = array('L', 'R', 'R', 'R', 'R');


            $pdf->styleRect($LIMITACION); /*margin de celdas*/
            $pdf->BorderTable($LIMITACION); /*para activar color de la fila*/

            $pdf->SetWidths($tamanio); /*tamaño de celdas array*/
            $pdf->SetAligns($aling); /*alineacion de texto en celda array*/
            $pdf->SetFont(FONT_TICKET_R, '', $SIZEFONTDETALLE - 1);

            $pdf->RowTicket($filasCabeceras);
        }

        // FIN HEADER TABLE TICKET

        // INICIO DATOS FOOTHER TICKETS
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -", $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "Total con letra: " . $pdf->num2letras(str_replace("$", "", str_replace(",", "", number_format($TOTALGENERAL ?? 1, 4)))), $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y + 5);
        $pdf->printColorTicket('negro', 7, '#ffffff');
        $pdf->MultiCell($texX, $texY, "Gracias por su compra!!", $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y + 5);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "Te esperamos pronto en " . $dato['INFO']['EMPRESA_NOMBRE_COMERCIAL'], $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y + 5);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "Visitanos en " . $dato['INFO']['EMPRESA_PAGINA_WEB'], $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "E-Mail: " . $dato['INFO']['EMPRESA_CORREO_ELECTRONICO'], $LIMITACION, 'C', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, $dato['INFO']['EMPRESA_TELEFONO'], $LIMITACION, 'C', false);

        // FIN DATOS FOOTHER TICKETS

        // INICIO ETIQUETA FOOTER
        $y = $pdf->GetY() + 5;
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, utf8_decode("Fecha de impresión " . date("Y-m-d H:i:s")), $LIMITACION, 'R', false);

        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);
        $pdf->printColorTicket('negro', 5, '#ffffff');
        $pdf->MultiCell($texX, $texY, "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - O R I G I N A L", $LIMITACION, 'C', false);
        // FIN ETIQUETA FOOTER

        switch ($dato['SETTINGS']['action']) {
            case 'DESCARGAR':
                $filename = $dato['SETTINGS']['fileName'] . "-" . date("d-m-Y His") . ".pdf";
                $pdf->Output('D', utf8_decode($filename));
                break;
            case 'VER':
                $filename = $dato['SETTINGS']['fileName'] . "-" . date("d-m-Y His") . ".pdf";
                $pdf->Output('I', utf8_decode($filename));
                break;
            case 'IMPRIMIR':
                $filename = $dato['SETTINGS']['fileName'] . "-" . date("d-m-Y His") . ".pdf";
                $pdf->AutoPrint(true);
                $pdf->Output('I', utf8_decode($filename));
                break;
        }
        exit();
    }

}
