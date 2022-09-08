<?php

namespace App\Http\Dto;

use App\Http\Controllers\Controller;

class DTOCATSexo extends Controller
{
    protected $id_cat_sexo;
    protected $sexo;
    protected $abreviatura;
    protected $activo;

    /**
     * DTOCATSexo constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getIdCatSexo()
    {
        return $this->id_cat_sexo;
    }

    /**
     * @param mixed $id_cat_sexo
     */
    public function setIdCatSexo($id_cat_sexo): void
    {
        $this->id_cat_sexo = $id_cat_sexo;
    }

    /**
     * @return mixed
     */
    public function getSexo()
    {
        return $this->sexo;
    }

    /**
     * @param mixed $sexo
     */
    public function setSexo($sexo): void
    {
        $this->sexo = $sexo;
    }

    /**
     * @return mixed
     */
    public function getAbreviatura()
    {
        return $this->abreviatura;
    }

    /**
     * @param mixed $abreviatura
     */
    public function setAbreviatura($abreviatura): void
    {
        $this->abreviatura = $abreviatura;
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
