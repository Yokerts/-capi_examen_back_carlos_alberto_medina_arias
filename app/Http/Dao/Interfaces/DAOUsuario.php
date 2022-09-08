<?php

namespace App\Http\Dao\Interfaces;

interface DAOUsuario
{
    public function all(&$paginacion);

    public function create();

    public function update();

    public function delete();

    public function show();

    public function showForTypeUser();

}
