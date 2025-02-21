<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Helpers\DateTimeFormatter;
use Illuminate\Support\Facades\Log;


class MssqlController extends Controller
{
    public function index(Request $request) {
        $data = Customers::query();
        $data = $data->get();
    }
}
