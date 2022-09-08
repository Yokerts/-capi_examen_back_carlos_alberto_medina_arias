<?php

namespace App\Http\Dao\Implement;

use App\Http\Dao\Interfaces\DAOConfiguracion;
use App\Http\Dto\DTOConfiguracion;
use Illuminate\Support\Facades\DB;

class IDAOConfiguracion extends DTOConfiguracion implements DAOConfiguracion
{

    public function create()
    {
        $inset = DB::table('configuracion')
            ->insertGetId([
                "id_usuario" => $this->getIdUsuario(),
                "tiempo_toast" => $this->getTiempoToast(),
                "tipo_menu" => $this->getTipoMenu()
            ]);

        return $inset;
    }

    public function update()
    {
        $up = DB::table('configuracion')->where('configuracion.id_usuario', '=', $this->getIdUsuario());
        if (!empty($this->getTiempoToast())) {
            $update = $up->update([
                "tiempo_toast" => $this->getTiempoToast()
            ]);
        }
        if (!empty($this->getTipoMenu())) {
            $update = $up->update([
                "tipo_menu" => $this->getTipoMenu()
            ]);
        }
        return $update;
    }

    public function show()
    {
        $row = $this->cfg();
        if ($row === null) {
            $this->setTiempoToast(5);
            $this->setTipoMenu(1);
            $this->create();
            $row = $this->cfg();
        }
        return (object)$row;
    }

    public function cfg()
    {
        $info = DB::table('configuracion')->select('configuracion.*')->where('configuracion.id_usuario', '=', $this->getIdUsuario())->first();
        return $info;
    }
}
