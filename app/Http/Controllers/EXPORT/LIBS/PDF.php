<?php
namespace App\Http\Controllers\EXPORT\LIBS;

class PDF extends PDF_JAVASCRIPT
{
    var $widths;
    var $aligns;
    var $styleRect;
    var $borderTable;
    var $borderMulticell;

    function setDATOS($value)
    {
        $this->DATOS = $value;
    }

    function getDATOS()
    {
        return $this->DATOS;
    }

    function setReferenceWatermark($value)
    {
        $this->referenceWatermark = $value;
    }

    function getReferenceWatermark()
    {
        return $this->referenceWatermark;
    }

    function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle = 0)
    {
        $font_angle += 90 + $txt_angle;
        $txt_angle *= M_PI / 180;
        $font_angle *= M_PI / 180;
        $txt_dx = cos($txt_angle);
        $txt_dy = sin($txt_angle);
        $font_dx = cos($font_angle);
        $font_dy = sin($font_angle);

        $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', $txt_dx, $txt_dy, $font_dx, $font_dy, $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        if ($this->ColorFlag)
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        $this->_out($s);
    }

    function WordWrapss($text, $maxwidth)
    {
        $text = trim($text);
        if ($text === '')
            return 0;
        $space = $this->GetStringWidth(' ');
        $lines = explode("\n", $text);
        $text = '';
        $count = 0;

        foreach ($lines as $line) {
            $words = preg_split('/ +/', $line);
            $width = 0;

            foreach ($words as $word) {
                $wordwidth = $this->GetStringWidth($word);
                if ($wordwidth > $maxwidth) {
                    // Word is too long, we cut it
                    for ($i = 0; $i < strlen($word); $i++) {
                        $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
                        if ($width + $wordwidth <= $maxwidth) {
                            $width += $wordwidth;
                            $text .= substr($word, $i, 1);
                        } else {
                            $width = $wordwidth;
                            $text = rtrim($text) . "\n" . substr($word, $i, 1);
                            $count++;
                        }
                    }
                } elseif ($width + $wordwidth <= $maxwidth) {
                    $width += $wordwidth + $space;
                    $text .= $word . ' ';
                } else {
                    $width = $wordwidth + $space;
                    $text = rtrim($text) . "\n" . $word . ' ';
                    $count++;
                }
            }
            $text = rtrim($text) . "\n";
            $count++;
        }
        $text = rtrim($text);
        return $count;
    }

    function WordWrap(&$text, $maxwidth)
    {
        $text = trim($text);
        if ($text === '')
            return 0;
        $space = $this->GetStringWidth(' ');
        $lines = explode("\n", $text);
        $text = '';
        $count = 0;

        foreach ($lines as $line) {
            $words = preg_split('/ +/', $line);
            $width = 0;

            foreach ($words as $word) {
                $wordwidth = $this->GetStringWidth($word);
                if ($wordwidth > $maxwidth) {
                    // Word is too long, we cut it
                    for ($i = 0; $i < strlen($word); $i++) {
                        $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
                        if ($width + $wordwidth <= $maxwidth) {
                            $width += $wordwidth;
                            $text .= substr($word, $i, 1);
                        } else {
                            $width = $wordwidth;
                            $text = rtrim($text) . "\n" . substr($word, $i, 1);
                            $count++;
                        }
                    }
                } elseif ($width + $wordwidth <= $maxwidth) {
                    $width += $wordwidth + $space;
                    $text .= $word . ' ';
                } else {
                    $width = $wordwidth + $space;
                    $text = rtrim($text) . "\n" . $word . ' ';
                    $count++;
                }
            }
            $text = rtrim($text) . "\n";
            $count++;
        }
        $text = rtrim($text);
        return $text;
    }

    function fill($f)
    {
        //juego de arreglos de relleno
        $this->fill = $f;
    }

    function BorderMulticell($value)
    {
        $this->borderMulticell = $value;
    }

    function RowTicket($data, $lh = 1.7)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = $lh * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            if ($this->borderTable) {
                $this->Rect($x, $y, $w, $h, $this->styleRect);
            }
            //Print the text
            $this->MultiCell($w, 1.7, $data[$i], $this->borderMulticell, $a, $fill = false);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw =& $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $this->SetY(13);
            $this->SetX(12);
        }
    }//borde

    /**
     ** Esto genera la parte de cabecera del detalle
     **/
    function CabeceraDetalle($_CONFIGCELDA, $cabecera, $TPapelH, $y, &$_EXCELCABECERADETALLE)
    {

        $wLabel = 30;
        $wValue = 70;

        $wL = (($wLabel * $TPapelH) / 100) / 2;
        $wV = (($wValue * $TPapelH) / 100) / 2;

        $_EXCELCABECERADETALLE = array();

        $this->SetY($y);

        $i = 0;
        $z = 0;

        if (count($cabecera) > 0) {
            /*Validar si es array la cabecera para evitar errores*/
            if (!is_array($cabecera)) {
                $cabecera = array();
            }

            if ($_CONFIGCELDA['SHOW_HIDDEN_COLUMNA_CABECERA'] != "") {
                $SHOW_HIDDEN_COLUMNA_CABECERA = explode(",", $_CONFIGCELDA['SHOW_HIDDEN_COLUMNA_CABECERA']);
            } else {
                /*Para hacer visible celdas de cabecera detalle*/
                foreach ($cabecera as $key => $value) {
                    $SHOW_HIDDEN_COLUMNA_CABECERA[] = "SHOW";
                }
            }

            $COUN = 0;
            foreach ($cabecera as $key => $value) {
                if ($SHOW_HIDDEN_COLUMNA_CABECERA[$COUN] == "SHOW") {
                    $cabecera2[$key] = $value;
                }
                $COUN++;
            }

            if (is_array($cabecera2)) {
                $cabecera = $cabecera2;
            }

            /*Para dar el formato para imprimir las filas*/
            foreach ($cabecera as $key => $row) {
                $i++;

                $DATOSR[$z][] = utf8_decode($key . ":");
                $DATOSR[$z][] = utf8_decode($row);

                if (is_int(($i / 2))) {
                    $z++;
                }
            }

            $_EXCELCABECERADETALLE = $DATOSR;

            /*Para meter otra daros a la fila cuando sea impar para que no se descuadre la cabecera*/
            $restante = 0;
            // Todo REVISAR AQUI EL FUNCIONAMIENTO de la variable $restante
            foreach ($DATOSR as $key => $row) {
                $conteocabecera = count($DATOSR[$key]);
                if ($conteocabecera < 4) {
                    $restante = 4 - $conteocabecera;
                }
                for ($p = 0; $p < $restante; $p++) {
                    $DATOSR[$key][] = "";
                }
            }

            /*Para agregar el tamaño*/
            foreach ($DATOSR as $key => $value) {
                $tamanio[] = $wL;
                $tamanio[] = $wV;
            }

//            dd($tamanio);

            /*Para agregar la alineacion*/
            foreach ($DATOSR as $key => $value) {
                $aling[] = 'L';
                $aling[] = 'L';
            }

            /*Para imprimir los datos fila por fila*/
            foreach ($DATOSR as $key => $row) {
                $this->SetFillColor(240, 240, 240); /*Fondo verde de celda*/
                $this->SetDrawColor(230, 230, 230); /*Color margin de celdas*/
                $this->SetTextColor(0, 0, 0); /*Letra color blanco*/

                $this->styleRect(0); /*margin de celdas*/
                $this->BorderTable(1); /*para activar color de la fila*/

                $this->SetWidths($tamanio); /*tamaño de celdas array*/
                $this->SetAligns($aling); /*alineacion de texto en celda array*/
                $this->SetFont('arial', 'B', 7);

                $this->SetLeftMargin(12);
                $this->Row($DATOSR[$key]);
            }

        }

    }

    function styleRect($s)
    {
        $this->styleRect = $s;
    }

    function BorderTable($value)
    {
        $this->borderTable = $value;
    }

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($data, $lh = 4)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = $lh * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            if ($this->borderTable) {
                $this->Rect($x, $y, $w, $h, $this->styleRect);
            }
            //Print the text
            $this->MultiCell($w, $lh, $data[$i], $this->borderMulticell, $a, $fill = false);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    /**
     ** Esto genera la cabecera de la tabla
     **/
    function DetallesHeadTable($cabecera, $w, $h, $x, $y, $bandera = true, $TPapelH, $_CONFIGCELDA, &$_EXCELHEADERDETALLE)
    {

        if ($_CONFIGCELDA['TAMANIO_CELDA_DETALLE'] != "") {
            $tamanio = explode(",", $_CONFIGCELDA['TAMANIO_CELDA_DETALLE']);
        } else {
            foreach ($cabecera as $key => $value) {
                $tamanio[] = $w;
            }
        }

        if ($_CONFIGCELDA['ALINEACION_CELDA_DETELLE'] != "") {
            $aling = explode(",", $_CONFIGCELDA['ALINEACION_CELDA_DETELLE']);
        } else {
            foreach ($cabecera as $key => $value) {
                $aling[] = 'C';
            }
        }

        if ($_CONFIGCELDA['SHOW_HIDDEN_COLUMNA'] != "") {
            $hiddencelda = explode(",", $_CONFIGCELDA['SHOW_HIDDEN_COLUMNA']);
        } else {
            foreach ($cabecera as $key => $value) {
                $hiddencelda[] = "SHOW";
            }
        }


        unset($filasCabeceras);
        foreach ($cabecera as $key => $fila) {
            if ($hiddencelda[$key] == "SHOW") {
                $filasCabeceras[] = utf8_decode($fila);
            }
        }

        // inicio cambio de posicion de las columnas
        if ($_CONFIGCELDA['POSICION_CELDA_DETALLE'] != "") {
            $position = explode(",", $_CONFIGCELDA['POSICION_CELDA_DETALLE']);
        } else {
            foreach ($cabecera as $key => $value) {
                $position[] = $key;
            }
        }

        foreach ($position as $key => $positionkey) {
            $filasCabecerasTemp[] = $filasCabeceras[$positionkey];
        }
        $filasCabeceras = $filasCabecerasTemp;
        $_EXCELHEADERDETALLE = $filasCabeceras;

        // fin cambio de posicion de las columnas

        // $this->SetFillColor(2,157,116); /*Fondo verde de celda este color viene de GeneratePDF*/
        $this->SetDrawColor(230, 230, 230); /*Color margin de celdas*/
        $this->SetTextColor(240, 255, 240); /*Letra color blanco*/

        $this->styleRect(0); /*margin de celdas*/
        $this->BorderTable(1); /*para activar color de la fila*/

        $this->SetWidths($tamanio); /*tamaño de celdas array*/
        $this->SetAligns($aling); /*alineacion de texto en celda array*/
        $this->SetFont('arial', 'B', 7);

        $this->SetLeftMargin(12);
        $this->Row($filasCabeceras);
    }

    /**
     ** Esto genera el cuerpo de la tabla
     **/
    function DetallesBodyTable($dato, $HTML_CAMPOS, $HTML_CAMPOS_NODO, $HTML_ETIQUETA_NODO, $TPapelH, $HTML_DATOS, $w, $h, $x, $y, $lineFin, $TPapelV, $_CONFIGCELDA, &$_EXCELBODYDETALLE)
    {

        $y = $this->GetY();
        $this->SetXY($x, $y);
        $this->SetFont('Arial', '', 6);
        $this->SetTextColor(3, 3, 3); //Color del texto: Negro
        $bandera = true; //Para alternar el relleno
        $numero = 1;

        /*
         * INICIO TABLA NORMAL:: TABLA DETALLE
         * */

        if (!is_array($HTML_CAMPOS)) {
            $HTML_CAMPOS = array();
        }

        if (!is_array($HTML_DATOS)) {
            $HTML_DATOS = array();
        }

        if ($_CONFIGCELDA['TAMANIO_CELDA_DETALLE'] != "") {
            $tamanio = explode(",", $_CONFIGCELDA['TAMANIO_CELDA_DETALLE']);
        } else {
            foreach ($HTML_CAMPOS as $key => $value) {
                $tamanio[] = $w;
            }
        }

        if ($_CONFIGCELDA['ALINEACION_CELDA_DETELLE'] != "") {
            $aling = explode(",", $_CONFIGCELDA['ALINEACION_CELDA_DETELLE']);
        } else {
            foreach ($HTML_CAMPOS as $key => $value) {
                $aling[] = 'C';
            }
        }


        if ($_CONFIGCELDA['SHOW_HIDDEN_COLUMNA'] != "") {
            $hiddencelda = explode(",", $_CONFIGCELDA['SHOW_HIDDEN_COLUMNA']);
        } else {
            foreach ($HTML_CAMPOS as $key => $value) {
                $hiddencelda[] = "SHOW";
            }
        }

        // inicio cambio de posicion de las columnas
        if ($_CONFIGCELDA['POSICION_CELDA_DETALLE'] != "") {
            $position = explode(",", $_CONFIGCELDA['POSICION_CELDA_DETALLE']);
        } else {
            foreach ($HTML_CAMPOS as $key => $value) {
                $position[] = $key;
            }
        }

        /*
         * FIN TABLA NORMAL:: TABLA DETALLE
         * */


        /*
         * INICIO NODO TABLA :: INICIO CAMBIO DE POSICION DEL NODO DE LAS COLUMNAS
         * */

        if (!is_array($HTML_CAMPOS_NODO)) {
            $HTML_CAMPOS_NODO = array();
        }

        if ($_CONFIGCELDA['SHOW_HIDDEN_COLUMNA_NODO'] != "") {
            $hiddencelda_nodo = explode(",", $_CONFIGCELDA['SHOW_HIDDEN_COLUMNA_NODO']);
        } else {
            foreach ($HTML_CAMPOS_NODO as $key => $value) {
                $hiddencelda_nodo[] = "SHOW";
            }
        }

        if ($_CONFIGCELDA['POSICION_CELDA_DETALLE_NODO'] != "") {
            $position_nodo = explode(",", $_CONFIGCELDA['POSICION_CELDA_DETALLE_NODO']);
        } else {
            foreach ($HTML_CAMPOS_NODO as $key => $value) {
                $position_nodo[] = $key;
            }
        }

        if ($_CONFIGCELDA['ALINEACION_CELDA_DETALLE_SUB_TABLA'] != "") {
            $aling_nodo = explode(",", $_CONFIGCELDA['ALINEACION_CELDA_DETALLE_SUB_TABLA']);
        } else {
            foreach ($HTML_CAMPOS_NODO as $key => $value) {
                $aling_nodo[] = 'C';
            }
        }

        if ($_CONFIGCELDA['TAMANIO_CELDA_DETALLE_SUB_TABLA'] != "") {
            $tamanio_nodo = explode(",", $_CONFIGCELDA['TAMANIO_CELDA_DETALLE_SUB_TABLA']);
        } else {
            $n_nodo = count($HTML_CAMPOS_NODO);
            foreach ($HTML_CAMPOS_NODO as $key => $value) {
                $tamanio_nodo[] = $TPapelH / $n_nodo;
            }
        }


        /*
         * FIN NODO TABLA :: INICIO CAMBIO DE POSICION DEL NODO DE LAS COLUMNAS
         * */


        // fin cambio de posicion de las columnas
        $keyDATOEXCEL = 0;
        foreach ($HTML_DATOS as $key1 => $row1) {
            unset($datosarray);
            foreach ($HTML_CAMPOS as $key2 => $row2) {
                if ($hiddencelda[$key2] == "SHOW") {
                    $datosarray[] = html_entity_decode(utf8_decode($row1[$row2['NOMBRE']])); //promero codifica los acentos y caracteres especiales, despues decodifica los entites de html
                }
            }

            // inicio cambio de posicion de las columnas
            unset($datosarrayTemp);
            foreach ($position as $key => $positionkey) {
                $datosarrayTemp[] = $datosarray[$positionkey];
            }
            $datosarray = $datosarrayTemp;
            $_EXCELBODYDETALLE[$keyDATOEXCEL] = $datosarray;
            $_EXCELBODYDETALLE[$keyDATOEXCEL]['_NODO'] = "";
            // fin cambio de posicion de las columnas

            $this->SetFillColor(230, 230, 230); /*Fondo gris de celda*/
            $this->SetDrawColor(230, 230, 230); /*Color margin de celdas*/
            $this->SetTextColor(0, 0, 0); /*Letra color blanco*/

            $this->styleRect($bandera); /*margin de celdas*/
            $this->BorderTable($bandera); /*para activar color de la fila*/

            $this->SetWidths($tamanio); /*tamaño de celdas array*/
            $this->SetAligns($aling); /*alineacion de texto en celda array*/
            $this->SetFont('arial', '', 7);

            $this->SetLeftMargin(12);
            $this->Row($datosarray);

            // INICIO WATERMARCK
            $this->printWATERMARCK($_CONFIGCELDA);
            // FIN WATERMARCK

            // INICIO ESTO ES PARA LOS QUE TENGAN SUB TABLAS
            if (!isset($row1['SUB_NODO_PDF_EXPORT'])) {
                $row1['SUB_NODO_PDF_EXPORT'] = array();
            }
            if (!is_array($row1['SUB_NODO_PDF_EXPORT'])) {
                $row1['SUB_NODO_PDF_EXPORT'] = array();
            }
            if (count($row1['SUB_NODO_PDF_EXPORT']) > 0) {

                if ($dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['SHOW_TITLE_SUB_TABLE']) {
                    /*
                    * INICIO :: PARA EL HEADER DDE LAS SUB TABLAS
                    * */

                    $y = $this->GetY();
                    $this->SetXY($x, $y);
                    $y = $this->GetY();
                    $this->printColor('gris', 8, '#e5e5e5');
                    $w = $TPapelH / count($HTML_ETIQUETA_NODO);
                    $this->printColor('celeste', NULL, '#888888');
                    $this->DetallesHeadSubTable($HTML_ETIQUETA_NODO, $w, 5, $x, $y, true, $TPapelH, $_CONFIGCELDA, $_EXCELHEADERDETALLE);

                    /*
                     * INICIO :: PARA EL HEADER DDE LAS SUB TABLAS
                     * */
                }

                if ($dato['CONFIGURAR_DATOS_ETIQUETAS_DETALLES']['SHOW_DATOS_SUB_TABLE']) {

                    $bandera = false;
                    foreach ($row1['SUB_NODO_PDF_EXPORT'] as $keySUB => $rowSUB) {

                        unset($datosarray);

                        if (count($HTML_CAMPOS_NODO) > 0) {
                            foreach ($HTML_CAMPOS_NODO as $key2 => $row2) {
                                if ($hiddencelda_nodo[$key2] == "SHOW") {
                                    $datosarray[] = html_entity_decode(utf8_decode($rowSUB[$row2['NOMBRE']])); //promero codifica los acentos y caracteres especiales, despues decodifica los entites de html
                                }
                            }
                        } else {
                            foreach ($rowSUB as $k => $d) {
                                $datosarray[] = $d;
                            }
                        }

                        // inicio cambio de posicion de las columnas
                        unset($datosarrayTemp);
                        foreach ($position_nodo as $key => $positionkey) {
                            $datosarrayTemp[] = $datosarray[$positionkey];
                        }

//                    dd($rowSUB, $datosarray, $datosarrayTemp);

                        $datosarray = $datosarrayTemp;

                        if (!is_array($_EXCELBODYDETALLE[$keyDATOEXCEL]['_NODO'])) {
                            $_EXCELBODYDETALLE[$keyDATOEXCEL]['_NODO'] = array();
                        }
                        $_EXCELBODYDETALLE[$keyDATOEXCEL]['_NODO'][] = $datosarray;
//                    dd($_EXCELBODYDETALLE);
                        // fin cambio de posicion de las columnas

                        $this->SetFillColor(240, 240, 240); /*Fondo gris de celda*/
                        $this->SetDrawColor(230, 230, 230); /*Color margin de celdas*/
                        $this->SetTextColor(0, 0, 0); /*Letra color blanco*/

                        $this->styleRect($bandera); /*margin de celdas*/
                        $this->BorderTable($bandera); /*para activar color de la fila*/

                        $this->SetWidths($tamanio_nodo); /*tamaño de celdas array*/
                        $this->SetAligns($aling_nodo); /*alineacion de texto en celda array*/
                        $this->SetFont('arial', '', 7);

                        $this->SetLeftMargin(12);
//                    dd($datosarray);
                        $this->Row($datosarray);

                        // INICIO WATERMARCK
                        $this->printWATERMARCK($_CONFIGCELDA);
                        // FIN WATERMARCK
                    }
                    $bandera = true;

                }

            } else {
                $bandera = !$bandera;
            }

            // FIN ESTO ES PARA LOS QUE TENGAN SUB TABLAS

            $keyDATOEXCEL++;
        }

        if (!is_array($_CONFIGCELDA['TEXTO_EXTRA'])) {
            $_CONFIGCELDA['TEXTO_EXTRA'] = array();
        }

        if (count($_CONFIGCELDA['TEXTO_EXTRA']) > 0) {
            $y = $this->GetY() + 10;
            foreach ($_CONFIGCELDA['TEXTO_EXTRA'] as $key => $row) {
                $this->SetXY($x, $y);
                $this->printColor('negroregular', 10, '#ffffff');
                // $TEXTO = $row['TEXTO'] . ' ' . $key . ' ' . $this->GetY();
                $TEXTO = $row['TEXTO'];
                $this->MultiCell(NULL, NULL, $this->DecodeText($TEXTO), 0, 'L', false);
                $y += 5;
                // INICIO WATERMARCK
                $this->printWATERMARCK($_CONFIGCELDA);
                // FIN WATERMARCK
            }
        } else {
            if ($this->PageNo() == 1) {
                $LimitPagePrintFila = ($TPapelV - $this->GetY()) / 4;

                for ($numero = 0; $numero <= $LimitPagePrintFila; $numero++) {
                    unset($datosarray);
                    foreach ($HTML_CAMPOS as $key2 => $row2) {
                        $datosarray[] = "";
                    }

                    $this->SetFillColor(240, 240, 240); /*Fondo gris de celda*/
                    $this->SetDrawColor(230, 230, 230); /*Color margin de celdas*/
                    $this->SetTextColor(0, 0, 0); /*Letra color blanco*/

                    $this->styleRect($bandera); /*margin de celdas*/
                    $this->BorderTable($bandera); /*para activar color de la fila*/

                    $this->SetWidths($tamanio); /*tamaño de celdas array*/
                    $this->SetAligns($aling); /*alineacion de texto en celda array*/
                    $this->SetFont('arial', 'B', 7);

                    $this->SetX($x);
                    $this->Row($datosarray);

                    $bandera = !$bandera;
                    // INICIO WATERMARCK
                    $this->printWATERMARCK($_CONFIGCELDA);
                    // FIN WATERMARCK
                }
            }
        }
    }

    function printWATERMARCK($_CONFIGCELDA = NULL, $MAX = NULL)
    {
        switch ($this->DATOS['SETTINGS']['size']) {
            case 'CartaVertical':
                $WATERMARCKX = 33;
                $WATERMARCKY = 120;
                if ($MAX == NULL) {
                    $MAX = 195;
                }
                break;
            case 'CartaHorizontal':
                $WATERMARCKX = 70;
                $WATERMARCKY = 75;
                if ($MAX == NULL) {
                    $MAX = 160;
                }
                break;
            case 'OficioVertical':
                $WATERMARCKX = 33;
                $WATERMARCKY = 120;
                if ($MAX == NULL) {
                    $MAX = 195;
                }
                break;
            case 'OficioHorizontal':
                $WATERMARCKX = 105;
                $WATERMARCKY = 75;
                if ($MAX == NULL) {
                    $MAX = 160;
                }
                break;
        }

        if ($this->PageNo() == 1 && $this->referenceWatermark == 1 && $this->GetY() >= $MAX) {
            $this->referenceWatermark = 0;
            if ($_CONFIGCELDA['MARCA_DE_AGUA'] != "") {

                $rootlogo = env('DOC_IMAGE_TO_PDF') . $_CONFIGCELDA['MARCA_DE_AGUA'];
                if (file_exists($rootlogo)) {
                    $this->Image($rootlogo, $WATERMARCKX, $WATERMARCKY, '150', null);
                }
            }
        }

    }

    function printColor($color, $size, $hex_bg = NULL, $hex_txt = NULL)
    {
        if ($hex_bg === NULL) {
            $RGB_BG = array(
                'r' => 0,
                'g' => 0,
                'b' => 0
            );
        } else {
            $RGB_BG = $this->HexToRgb($hex_bg);
        }

        $this->SetFillColor($RGB_BG['r'], $RGB_BG['g'], $RGB_BG['b']);
        if ($color != NULL) {
            switch ($color) {
                case 'negroregular':
                    $this->SetTextColor(0, 0, 0);
                    $this->SetFont('Arial', '', $size);
                    break;
                case 'negro':
                    $this->SetTextColor(0, 0, 0);
                    $this->SetFont('Arial', 'B', $size);
                    break;
                case 'rojo':
                    $this->SetTextColor(255, 255, 255); //Letra color blanco
                    $this->SetFont('Arial', 'B', $size);
                    break;
                case 'celeste':
                    $this->SetTextColor(255, 255, 255); //Letra color blanco
                    $this->SetFont('Arial', 'B', $size);
                    break;
                case 'gris':
                    $this->SetTextColor(3, 3, 3); //Color del texto: Negro
                    $this->SetFont('Arial', '', $size);
                    break;
            }
        } else {
            if ($hex_txt === NULL) {
                $RGB_TXT = array(
                    'r' => 0,
                    'g' => 0,
                    'b' => 0
                );
            } else {
                $RGB_TXT = $this->HexToRgb($hex_txt);
            }
            $this->SetTextColor($RGB_TXT['r'], $RGB_TXT['g'], $RGB_TXT['b']);
            $this->SetFont('Arial', '', $size);
        }
        return;
    }

    public function HexToRgb($hex, $alpha = false)
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 6) {
            $rgb['r'] = hexdec(substr($hex, 0, 2));
            $rgb['g'] = hexdec(substr($hex, 2, 2));
            $rgb['b'] = hexdec(substr($hex, 4, 2));
        } else if (strlen($hex) == 3) {
            $rgb['r'] = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $rgb['g'] = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $rgb['b'] = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $rgb['r'] = '0';
            $rgb['g'] = '0';
            $rgb['b'] = '0';
        }
        if ($alpha) {
            $rgb['a'] = $alpha;
        }
        return $rgb;
    }

    /**
     ** Esto genera la cabecera de la tabla
     **/
    function DetallesHeadSubTable($cabecera, $w, $h, $x, $y, $bandera = true, $TPapelH, $_CONFIGCELDA, &$_EXCELHEADERDETALLE)
    {

        if ($_CONFIGCELDA['ALINEACION_CELDA_DETALLE_SUB_TABLA'] != "") {
            $aling = explode(",", $_CONFIGCELDA['ALINEACION_CELDA_DETALLE_SUB_TABLA']);
        } else {
            foreach ($cabecera as $key => $value) {
                $aling[] = 'C';
            }
        }

        if ($_CONFIGCELDA['TAMANIO_CELDA_DETALLE_SUB_TABLA'] != "") {
            $tamanio = explode(",", $_CONFIGCELDA['TAMANIO_CELDA_DETALLE_SUB_TABLA']);
        } else {
            $n_nodo = count($cabecera);
            foreach ($cabecera as $key => $value) {
                $tamanio[] = $TPapelH / $n_nodo;
            }
        }

        foreach ($cabecera as $key => $value) {
            $hiddencelda[] = "SHOW";
        }


//        $filasCabeceras = array();

//        unset($filasCabeceras);
//        dd($hiddencelda, $cabecera);
        foreach ($cabecera as $key => $fila) {
            if ($hiddencelda[$key] == "SHOW") {
                $filasCabeceras[] = utf8_decode($fila);
            }
        }

        // inicio cambio de posicion de las columnas
        foreach ($cabecera as $key => $value) {
            $position[] = $key;
        }

        foreach ($position as $key => $positionkey) {
            $filasCabecerasTemp[] = $filasCabeceras[$positionkey];
        }
        $filasCabeceras = $filasCabecerasTemp;
        $_EXCELHEADERDETALLE = $filasCabeceras;

        // fin cambio de posicion de las columnas

        $this->SetFillColor(245, 245, 245); /*Fondo verde de celda*/
        $this->SetDrawColor(230, 230, 230); /*Color margin de celdas*/
        $this->SetTextColor(70, 70, 70); /*Letra color blanco*/


        $this->styleRect(0); /*margin de celdas*/
        $this->BorderTable(1); /*para activar color de la fila*/

        $this->SetWidths($tamanio); /*tamaño de celdas array*/
        $this->SetAligns($aling); /*alineacion de texto en celda array*/
        $this->SetFont('arial', 'I', 7);

        $this->SetLeftMargin(12);
        $this->Row($filasCabeceras);
    }

    //***** Aquí comienza código para ajustar texto *************
    //***********************************************************

    function DecodeText($cadena)
    {
        return utf8_decode(html_entity_decode($cadena));
    }

    function printColorTicket($color, $size, $hex = NULL)
    {
        if ($hex === NULL) {
            $RGB = array(
                'r' => 0,
                'g' => 0,
                'b' => 0
            );
        } else {
            $RGB = $this->HexToRgb($hex);
        }

        $this->SetFillColor($RGB['r'], $RGB['g'], $RGB['b']);
        if ($color != NULL) {
            switch ($color) {
                case 'negro':
                    $this->SetTextColor(0, 0, 0);
                    $this->SetFont(FONT_TICKET_R, '', $size);
                    break;
                case 'rojo':
                    $this->SetTextColor(255, 255, 255); //Letra color blanco
                    $this->SetFont(FONT_TICKET_R, '', $size);
                    break;
                case 'celeste':
                    $this->SetTextColor(255, 255, 255); //Letra color blanco
                    $this->SetFont(FONT_TICKET_R, '', $size);
                    break;
                case 'gris':
                    $this->SetTextColor(3, 3, 3); //Color del texto: Negro
                    $this->SetFont(FONT_TICKET_R, '', $size);
                    break;
            }
        }
        return;
    }

    //Patch to also work with CJK double-byte text

    function CellFitSpace($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        $this->CellFit($w, $h, $txt, $border, $ln, $align, $fill, $link, false, false);
    }

    function CellFit($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $scale = false, $force = true)
    {
        //Get string width
        $str_width = $this->GetStringWidth($txt);

        //Calculate ratio to fit cell
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        if ($str_width == 0) {
            $str_width = 1;
        }
        $ratio = ($w - $this->cMargin * 2) / $str_width;

        $fit = ($ratio < 1 || ($ratio > 1 && $force));
        if ($fit) {
            if ($scale) {
                //Calculate horizontal scaling
                $horiz_scale = $ratio * 100.0;
                //Set horizontal scaling
                $this->_out(sprintf('BT %.2F Tz ET', $horiz_scale));
            } else {
                //Calculate character spacing in points
                $char_space = ($w - $this->cMargin * 2 - $str_width) / max($this->MBGetStringLength($txt) - 1, 1) * $this->k;
                //Set character spacing
                $this->_out(sprintf('BT %.2F Tc ET', $char_space));
            }
            //Override user alignment (since text will fill up cell)
            $align = '';
        }

        //Pass on to Cell method
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);

        //Reset character spacing/horizontal scaling
        if ($fit)
            $this->_out('BT ' . ($scale ? '100 Tz' : '0 Tc') . ' ET');
    }
    //************** Fin del código para ajustar texto *****************
    //******************************************************************

    function MBGetStringLength($s)
    {
        if ($this->CurrentFont['type'] == 'Type0') {
            $len = 0;
            $nbbytes = strlen($s);
            for ($i = 0; $i < $nbbytes; $i++) {
                if (ord($s[$i]) < 128)
                    $len++;
                else {
                    $len++;
                    $i++;
                }
            }
            return $len;
        } else
            return strlen($s);
    }

    function AutoPrint($dialog = false)
    {
        //Open the print dialog or start printing immediately on the standard printer
        $param = ($dialog ? 'true' : 'false');
        // $script="print($param);";
        $script = "print($param);";
        $this->IncludeJS($script);
    }

    function AutoPrintToPrinter($server, $printer, $dialog = false)
    {
        //Print on a shared printer (requires at least Acrobat 6)
        $script = "var pp = getPrintParams();";
        if ($dialog)
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
        else
            $script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
        $script .= "pp.printerName = '\\\\\\\\" . $server . "\\\\" . $printer . "';";
        $script .= "print(pp);";
        $this->IncludeJS($script);
    }

    public function num2letras($num, $fem = false, $dec = true)
    {
        $matuni[2] = "dos";
        $matuni[3] = "tres";
        $matuni[4] = "cuatro";
        $matuni[5] = "cinco";
        $matuni[6] = "seis";
        $matuni[7] = "siete";
        $matuni[8] = "ocho";
        $matuni[9] = "nueve";
        $matuni[10] = "diez";
        $matuni[11] = "once";
        $matuni[12] = "doce";
        $matuni[13] = "trece";
        $matuni[14] = "catorce";
        $matuni[15] = "quince";
        $matuni[16] = "dieciseis";
        $matuni[17] = "diecisiete";
        $matuni[18] = "dieciocho";
        $matuni[19] = "diecinueve";
        $matuni[20] = "veinte";
        $matunisub[2] = "dos";
        $matunisub[3] = "tres";
        $matunisub[4] = "cuatro";
        $matunisub[5] = "quin";
        $matunisub[6] = "seis";
        $matunisub[7] = "sete";
        $matunisub[8] = "ocho";
        $matunisub[9] = "nove";

        $matdec[2] = "veint";
        $matdec[3] = "treinta";
        $matdec[4] = "cuarenta";
        $matdec[5] = "cincuenta";
        $matdec[6] = "sesenta";
        $matdec[7] = "setenta";
        $matdec[8] = "ochenta";
        $matdec[9] = "noventa";
        $matsub[3] = 'mill';
        $matsub[5] = 'bill';
        $matsub[7] = 'mill';
        $matsub[9] = 'trill';
        $matsub[11] = 'mill';
        $matsub[13] = 'bill';
        $matsub[15] = 'mill';
        $matmil[4] = 'millones';
        $matmil[6] = 'billones';
        $matmil[7] = 'de billones';
        $matmil[8] = 'millones de billones';
        $matmil[10] = 'trillones';
        $matmil[11] = 'de trillones';
        $matmil[12] = 'millones de trillones';
        $matmil[13] = 'de trillones';
        $matmil[14] = 'billones de trillones';
        $matmil[15] = 'de billones de trillones';
        $matmil[16] = 'millones de billones de trillones';


        //Agregado
        $float = explode('.', $num);
        $num = $float[0];

        $num = trim((string)@$num);
        if ($num[0] == '-') {
            $neg = 'menos ';
            $num = substr($num, 1);
        } else
            $neg = '';
        $num = $num == 0 ? '0.0' : $num;
        while ($num[0] == '0') $num = substr($num, 1);
        if ($num[0] < '1' or $num[0] > 9) $num = '0' . $num;
        $zeros = true;
        $punt = false;
        $ent = '';
        $fra = '';
        for ($c = 0; $c < strlen($num); $c++) {
            $n = $num[$c];
            if (!(strpos(".,'''", $n) === false)) {
                if ($punt) break;
                else {
                    $punt = true;
                    continue;
                }

            } elseif (!(strpos('0123456789', $n) === false)) {
                if ($punt) {
                    if ($n != '0') $zeros = false;
                    $fra .= $n;
                } else

                    $ent .= $n;
            } else

                break;

        }
        $ent = '     ' . $ent;
        if ($dec and $fra and !$zeros) {
            $fin = ' coma';
            for ($n = 0; $n < strlen($fra); $n++) {
                if (($s = $fra[$n]) == '0')
                    $fin .= ' cero';
                elseif ($s == '1')
                    $fin .= $fem ? ' una' : ' un';
                else
                    $fin .= ' ' . $matuni[$s];
            }
        } else
            $fin = '';
        if ((int)$ent === 0) return 'Cero ' . $fin;
        $tex = '';
        $sub = 0;
        $mils = 0;
        $neutro = false;
        while (($num = substr($ent, -3)) != '   ') {
            $ent = substr($ent, 0, -3);
            if (++$sub < 3 and $fem) {
                $matuni[1] = 'una';
                $subcent = 'os'; /* MODIFICADO as */
            } else {
                $matuni[1] = $neutro ? 'un' : 'uno';
                $subcent = 'os';
            }
            $t = '';
            $n2 = substr($num, 1);
            if ($n2 == '00') {
            } elseif ($n2 < 21)
                $t = ' ' . $matuni[(int)$n2];
            elseif ($n2 < 30) {
                $n3 = $num[2];
                if ($n3 != 0) $t = 'i' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            } else {
                $n3 = $num[2];
                if ($n3 != 0) $t = ' y ' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }
            $n = $num[0];
            if ($n == 1) {
                $t = ' ciento' . $t;
            } elseif ($n == 5) {
                $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
            } elseif ($n != 0) {
                $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
            }
            if ($sub == 1) {
            } elseif (!isset($matsub[$sub])) {
                if ($num == 1) {
                    $t = ' mil';
                } elseif ($num > 1) {
                    $t .= ' mil';
                }
            } elseif ($num == 1) {
                $t .= ' ' . $matsub[$sub] . '?n';
            } elseif ($num > 1) {
                $t .= ' ' . $matsub[$sub] . 'ones';
            }
            if ($num == '000') $mils++;
            elseif ($mils != 0) {
                if (isset($matmil[$sub])) $t .= ' ' . $matmil[$sub];
                $mils = 0;
            }
            $neutro = true;
            $tex = $t . $tex;
        }
        $tex = $neg . substr($tex, 1) . $fin;
        // return ucfirst($tex); //oruginal
        $end_num = ucfirst($tex) . ' pesos ' . $float[1] . '/100 M.N.';
        return $end_num;
    }

    var $angle=0;

    function Rotate($angle,$x=-1,$y=-1)
    {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    function _endpage()
    {
        if($this->angle!=0)
        {
            $this->angle=0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

}

?>
