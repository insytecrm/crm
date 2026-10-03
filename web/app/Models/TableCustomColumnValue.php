<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'table_key',
    'record_id',
    'column_key',
    'value',
])]
class TableCustomColumnValue extends Model
{
    //
}
