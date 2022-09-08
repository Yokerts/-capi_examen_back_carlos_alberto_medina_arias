<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BackUpDataBaseController extends Controller
{
    public function backup()
    {
        date_default_timezone_set('America/Mexico_City');

        $url_backup = base_path() . '/db/backup/backup_' . date("Y_m_d_H_i_s") . "_" . env("DB_DATABASE") . '.sql';

        $tables = DB::select(DB::raw(sprintf("SELECT * FROM information_schema.TABLES WHERE (TABLE_TYPE = '%s' OR TABLE_TYPE = '%s') AND (TABLE_SCHEMA = '%s')", 'BASE TABLE', 'VIEW', env('DB_DATABASE'))));

        $respaldo = "";

        //loop through the tables
        foreach ($tables as $kkey => $iitem) {

            $respaldo .= "DROP TABLE $iitem->TABLE_NAME;";

            $estructura = DB::selectOne(sprintf('SHOW CREATE TABLE %s', $iitem->TABLE_NAME));
            $estructura = (array)$estructura;

            $DDL = array();

            foreach ($estructura as $kk => $rr) {
                $DDL[] = $rr;
            };

            $respaldo .= "nnnnnnnnnnnnnnnn" . $DDL[1] . ";nnnnnnnnnnnnnnnn";


            if ($iitem->TABLE_TYPE === "BASE TABLE") {

                $result = DB::table($iitem->TABLE_NAME)->get();

                foreach ($result as $key => $row) {
                    $respaldo .= "INSERT INTO $iitem->TABLE_NAME VALUES(";
                    $numItems = count((array)$row);
                    $i = 0;
                    foreach ($row  as $index => $value) {
                        $value = addslashes($value);
                        // $value = preg_replace('#n#', 'n', $value);
                        if (isset($value)) {
                            $respaldo .= '"' . $value . '"';
                        } else {
                            $respaldo .= '""';
                        }
                        if($i < $numItems - 1) {
                            $respaldo .= ',';
                        }
                        $i ++;
                    }
                    $respaldo .= ");nnnnnnnn";
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
