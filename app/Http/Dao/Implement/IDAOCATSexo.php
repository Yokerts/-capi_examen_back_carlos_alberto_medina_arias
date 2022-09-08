<?php

namespace App\Http\Dao\Implement;

use App\Http\Dao\Interfaces\DAOCATSexo;
use App\Http\Dto\DTOCATSexo;
use Illuminate\Support\Facades\DB;

class IDAOCATSexo extends DTOCATSexo implements DAOCATSexo
{

    public function all()
    {
        $result = DB::table('cat_sexo')->select('cat_sexo.*')->get();

        return $result;
    }

    public function create()
    {
        $inset = DB::table('cat_sexo')
            ->insertGetId([
                "id_cat_sexo" => $this->getIdCatSexo(),
                "sexo" => $this->getSexo(),
                "abreviatura" => $this->getAbreviatura(),
                "activo" => $this->getActivo(),
                "created_at" => $this->DATETIME(),
                "updated_at" => $this->DATETIME()
            ]);

        return $inset;
    }

    public function update()
    {
        $update = DB::table('cat_sexo')
            ->where('cat_sexo.id_cat_sexo', '=', $this->getIdCatSexo())
            ->update([
                "sexo" => $this->getSexo(),
                "abreviatura" => $this->getAbreviatura(),
                "activo" => $this->getActivo(),
                "updated_at" => $this->DATETIME()
            ]);

        return $update;
    }

    public function delete()
    {
        $del = DB::table('cat_sexo')->where('cat_sexo.id_cat_sexo', '=', $this->getIdCatSexo())->delete();

        return $del;
    }

    public function show()
    {
        $row = DB::table('cat_sexo')->select('cat_sexo.*')->where('cat_sexo.id_cat_sexo', '=', $this->getIdCatSexo())->first();

        if ($row) {
            return (object)$row;
        } else {
            return null;
        }
    }

    public function showForName()
    {
        $row = DB::table('cat_sexo')->select('cat_sexo.*')->where('cat_sexo.sexo', '=', $this->getSexo())->first();

        return $row;
    }

}
