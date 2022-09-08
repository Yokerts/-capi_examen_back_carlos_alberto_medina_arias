<?php

namespace App\Http\Dao\Implement;

use App\Http\Dao\Interfaces\DAOTipoUsuario;
use App\Http\Dto\DTOTipoUsuario;
use Illuminate\Support\Facades\DB;

class IDAOTipoUsuario extends DTOTipoUsuario implements DAOTipoUsuario
{

    public function all()
    {
        $result = DB::table('cat_tipo_usuario')->select('cat_tipo_usuario.*')->get();

        return $result;
    }

    public function create()
    {
        $inset = DB::table('cat_tipo_usuario')
            ->insertGetId([
                "id_cat_tipo_usuario" => $this->getIdCatTipoUsuario(),
                "tipo_usuario" => $this->getTipoUsuario(),
                "descripcion" => $this->getDescripcion(),
                "activo" => $this->getActivo(),
                "created_at" => $this->DATETIME(),
                "updated_at" => $this->DATETIME()
            ]);

        return $inset;
    }

    public function update()
    {
        $update = DB::table('cat_tipo_usuario')
            ->where('cat_tipo_usuario.id_cat_tipo_usuario', '=', $this->getIdCatTipoUsuario())
            ->update([
                "tipo_usuario" => $this->getTipoUsuario(),
                "descripcion" => $this->getDescripcion(),
                "activo" => $this->getActivo(),
                "updated_at" => $this->DATETIME()
            ]);

        return $update;
    }

    public function delete()
    {
        $del = DB::table('cat_tipo_usuario')->where('cat_tipo_usuario.id_cat_tipo_usuario', '=', $this->getIdCatTipoUsuario())->delete();

        return $del;
    }

    public function show()
    {
        $row = DB::table('cat_tipo_usuario')->select('cat_tipo_usuario.*')->where('cat_tipo_usuario.id_cat_tipo_usuario', '=', $this->getIdCatTipoUsuario())->first();

        if ($row) {
            return (object)$row;
        } else {
            return null;
        }
    }

    public function showForName()
    {
        $row = DB::table('cat_tipo_usuario')->select('cat_tipo_usuario.*')->where('cat_tipo_usuario.tipo_usuario', '=', $this->getTipoUsuario())->first();

        return $row;
    }
}
