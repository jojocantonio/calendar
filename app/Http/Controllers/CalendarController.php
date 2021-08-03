<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calendar;

class CalendarController extends Controller
{
    public function home()
    {
        return view('welcome');
    }

    public function index()
    {
        return response()->json(Calendar::orderBy('created_at', 'desc')->get());
    }

    public function addEvent(Request $request)
    {
        $calendar = new Calendar;
        $calendar->event = $request->event;
        $calendar->from = $request->from;
        $calendar->to = $request->to;
        $calendar->days = json_encode($request->days);
        $calendar->save();

        return response()->json($calendar);
    }
}
