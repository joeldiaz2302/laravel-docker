<?php
/**
 * @author joel diaz
*/
namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
	public function getColumns(){
		return DB::getSchemaBuilder()->getColumnListing($this->getTable());
	}

	public function getPrimaryKey(){
		return $this->primaryKey;
	}
}