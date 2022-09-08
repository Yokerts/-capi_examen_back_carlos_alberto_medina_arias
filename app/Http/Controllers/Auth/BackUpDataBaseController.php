<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BackUpDataBaseController extends Controller
{
    public function backup()
    {
        $expresion = '/CREATE TABLE([^?=]+)PRIMARY KEY([^)]+)\)/';
        date_default_timezone_set('America/Mexico_City');

        $url_backup = base_path() . '/db/backup/backup_' . date("Y_m_d_H_i_s") . "_" . env("DB_DATABASE_AUTH") . '.sql';

        $tables = DB::select(DB::raw(sprintf("SELECT * FROM information_schema.TABLES WHERE (TABLE_TYPE = '%s' OR TABLE_TYPE = '%s') AND (TABLE_SCHEMA = '%s')", 'BASE TABLE', 'VIEW', env('DB_DATABASE'))));

        $respaldo = "";

        //loop through the tables
        foreach ($tables as $kkey => $iitem) {

            $respaldo .= "DROP TABLE IF EXISTS $iitem->TABLE_NAME;";

            $estructura = DB::selectOne(sprintf('SHOW CREATE TABLE %s', $iitem->TABLE_NAME));
            $estructura = (array)$estructura;

            $TEMP = array();
            $DDL = array();

            foreach ($estructura as $kk => $rr) {
                $TEMP[] = $rr;
            };

            foreach ($TEMP as $kk => $rr) {
                if ($kk == 0) {
                    $DDL[] = $rr;
                } else {
                    $flag = preg_match($expresion, $rr, $matches, PREG_OFFSET_CAPTURE);
                    if ($flag) {
                        if (count($matches) >= 1) {
                            $DDL[] = $matches[0][0]."\n)";
                        } else {
                            $DDL[] = $rr;
                        }
                    } else {
                        $DDL[] = $rr;
                    }
                }
            };

//            dd($DDL);

            $respaldo .= "nnnnnnnnnnnnnnnn" . $DDL[1] . ";nnnnnnnnnnnnnnnn";


            if ($iitem->TABLE_TYPE === "BASE TABLE") {

                $result = DB::table($iitem->TABLE_NAME)->get();

                $final = count($result) - 1;

                foreach ($result as $key => $row) {
                    if ($key === 0) {
                        $respaldo .= "INSERT INTO $iitem->TABLE_NAME VALUES\n(";
                    } else {
                        $respaldo .= "(";
                    }
                    $numItems = count((array)$row);
                    $i = 0;
                    foreach ($row as $index => $value) {
                        if (gettype($value) === "string") {
                            $value = addslashes($value);
                        }
                        // $value = preg_replace('#n#', 'n', $value);
                        switch (gettype($value)) {
                            case "string":
                                $respaldo .= '"' . utf8_decode($value) . '"';
                                break;
                            case "integer":
                                $respaldo .= $value;
                                break;
                            default:
                                $respaldo .= $value === "" || $value === null ? 'NULL' : $value;
                        }
                        if ($i < $numItems - 1) {
                            $respaldo .= ',';
                        }
                        $i++;
                    }
                    if ($final === $key) {
                        $respaldo .= ");nnnnnnnn";
                    } else {
                        $respaldo .= "),nnnnnnnn";
                    }
                }

            }

            $respaldo .= "nnnnnnnnnnnnnnnn";

        }

        $respaldo = str_replace("nnnnnnnn", "\n", utf8_encode($respaldo));

        //save file
        $handle = fopen($url_backup, 'w+');
        fwrite($handle, $respaldo);
        fclose($handle);

    }

}
