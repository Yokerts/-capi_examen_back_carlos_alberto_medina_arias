<?php

namespace App\Http\Dto;

use App\Http\Controllers\Controller;

class DTOTipoUsuario extends Controller
{
    protected $id_cat_tipo_usuario;
    protected $tipo_usuario;
    protected $descripcion;
    protected $activo;

    /**
     * DTOTipoUsuario constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getIdCatTipoUsuario()
    {
        return $this->id_cat_tipo_usuario;
    }

    /**
     * @param mixed $id_cat_tipo_usuario
     */
    public function setIdCatTipoUsuario($id_cat_tipo_usuario): void
    {
        $this->id_cat_tipo_usuario = $id_cat_tipo_usuario;
    }

    /**
     * @return mixed
     */
    public function getTipoUsuario()
    {
        return $this->tipo_usuario;
    }

    /**
     * @param mixed $tipo_usuario
     */
    public function setTipoUsuario($tipo_usuario): void
    {
        $this->tipo_usuario = $tipo_usuario;
    }

    /**
     * @return mixed
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @param mixed $descripcion
     */
    public function setDescripcion($descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    /**
     * @return mixed
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * @param mixed $activo
     */
    public function setActivo($activo): void
    {
        $this->activo = $activo;
    }

}
