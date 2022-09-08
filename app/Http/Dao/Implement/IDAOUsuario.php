<?php

namespace App\Http\Dao\Implement;

use App\Http\Dao\Interfaces\DAOUsuario;
use App\Http\Dto\DTOUsuario;
use Illuminate\Support\Facades\DB;

class IDAOUsuario extends DTOUsuario implements DAOUsuario
{

    public function all(&$paginacion)
    {
        $result = DB::table('usuario')
            ->select(
                'usuario.*',
                DB::raw("CONCAT(IFNULL(usuario.nombre, ''), ' ', IFNULL(usuario.apellido_paterno, ''), ' ', IFNULL(usuario.apellido_materno, '')) AS usuario_nombre_completo")
            )
            ->get();
        return $result;
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function update()
    {
        // TODO: Implement update() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function show()
    {
        $row = DB::table('usuario')
            ->select(
                'usuario.*',
                DB::raw("CONCAT(IFNULL(usuario.nombre, ''), ' ', IFNULL(usuario.apellido_paterno, ''), ' ', IFNULL(usuario.apellido_materno, '')) AS usuario_nombre_completo")
            )
            ->where('usuario.id_usuario', '=', $this->getIdUsuario())
            ->first();
        return $row;
    }

    public function showForTypeUser()
    {
        $row = DB::table('usuario')
            ->select(
                'usuario.*',
                DB::raw("CONCAT(IFNULL(usuario.nombre, ''), ' ', IFNULL(usuario.apellido_paterno, ''), ' ', IFNULL(usuario.apellido_materno, '')) AS usuario_nombre_completo")
            )
            ->where('usuario.id_cat_tipo_usuario', '=', $this->getIdCatTipoUsuario())
            ->first();
        return $row;
    }
}
