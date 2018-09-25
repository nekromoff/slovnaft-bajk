<?php

namespace App\Http\Controllers;

use App\Station;
use App\StationsLog;
use App\SummaryDaily;
use Campo\UserAgent;
use Carbon\Carbon;
use Goutte;
use Goutte\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScraperController extends Controller
{
    private function initialize()
    {
        date_default_timezone_set('Europe/Bratislava');
        define('START_DAY', '2018-09-09');
        define('IGNORED_STATIONS', [121, 122]);
    }

    public function scrape()
    {
        self::initialize();
        $user_agent = UserAgent::random(['os_type' => ['Windows', 'Android', 'iOS']]);
        $crawler = Goutte::setHeader('user-agent', $user_agent)->request('GET', 'https://slovnaftbajk.sk/mapa-stanic');
        $crawler->filter('#stationTable table tr')->each(function ($row) {
            $line = $row->text();
            $parts = explode("\n", $line);
            $station_number = trim($parts[1]);
            // only process real stations, not test
            if ($station_number < 100) {
                $station_name = trim($parts[2]);
                if (strpos($parts[3], '/') !== false) {
                    $station_status = explode('/', trim($parts[3]));
                    $station_bicycles = $station_status[0];
                    $station_slots = $station_status[1];
                    $station = Station::updateOrCreate(['number' => $station_number], ['number' => $station_number, 'name' => $station_name]);
                    $log = new StationsLog;
                    $log->station_id = $station->id;
                    $log->free = $station_bicycles;
                    $log->slots = $station_slots;
                    $log->save();
                }
            }
        });
    }

    public function stats()
    {
        self::initialize();
        date_default_timezone_set('Europe/Bratislava');
        $last_week = Carbon::now()->subDays(7)->toDateString();
        $stations = Station::orderBy('name')->get()->keyBy('id');
        $average_bicycles = StationsLog::selectRaw('AVG(free) as free')->whereNotIn('station_id', IGNORED_STATIONS)->groupBy('station_id')->get();
        $daily_summary = SummaryDaily::where('day', '>=', $last_week)->orderBy('day', 'desc')->get();
        $total_bicycles = $daily_summary[0]->bicycles;
        $datetime = Carbon::now()->subDay();
        $free_bicycles = StationsLog::selectRaw('SUM(free)/60 as free, HOUR(created_at) as hour')->whereNotIn('station_id', IGNORED_STATIONS)->where('created_at', '>=', $datetime)->groupBy('hour')->orderBy('hour', 'desc')->get()->keyBy('hour');
        $daily_change = ($daily_summary[0]->bicycles / $daily_summary[1]->bicycles) * 100;
        $daily_change = $daily_change - 100;
        return view('home', ['stations' => $stations, 'daily_summary' => $daily_summary, 'total_bicycles' => $total_bicycles, 'free_bicycles' => $free_bicycles, 'daily_change' => $daily_change, 'average_bicycles' => $average_bicycles]);
    }

    public function generateOGImage(Request $request)
    {
        self::initialize();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        $daily_summary = SummaryDaily::where('day', '=', Carbon::today())->orderBy('day', 'desc')->first();
        if ($daily_summary) {
            //if today not yet available
            $total_bicycles = $daily_summary->bicycles;
        } else {
            // take bicycle number from yesterday
            $daily_summary = SummaryDaily::where('day', '=', Carbon::yesterday())->orderBy('day', 'desc')->first();
            $total_bicycles = $daily_summary->bicycles;
        }
        $image = new \claviska\SimpleImage();
        $image->fromNew(2400, 1260, 'white')
            ->rectangle(0, 0, 2400, 630, '#28a745', 'filled')
            ->text($total_bicycles, ['fontFile' => public_path('fonts/Tern-Regular.ttf'), 'size' => 550, 'color' => 'white', 'anchor' => 'top left', 'xOffset' => 800, 'yOffset' => 75])
            ->text('bicyklov', ['fontFile' => public_path('fonts/Tern-Regular.ttf'), 'size' => 300, 'color' => 'black', 'anchor' => 'top left', 'xOffset' => 650, 'yOffset' => 680])
            ->text('dnes v meste', ['fontFile' => public_path('fonts/Tern-Regular.ttf'), 'size' => 200, 'color' => 'black', 'anchor' => 'top left', 'xOffset' => 600, 'yOffset' => 1000])
            ->toScreen();
    }

    public function processDailySummary(Request $request)
    {
        self::initialize();
        if ($request->secret != 'agree') {
            return;
        }
        $yesterday = Carbon::yesterday();
        $stations_logs = StationsLog::selectRaw('SUM(free) as free, DATE(created_at) as day')->whereNotIn('station_id', IGNORED_STATIONS)->whereDate('created_at', '=', $yesterday)->groupBy('created_at')->orderBy('free', 'desc')->take(1)->first();
        $daily = StationsLog::selectRaw('AVG(free) as free, DATE(created_at) as day')->whereNotIn('station_id', IGNORED_STATIONS)->whereDate('created_at', '=', $yesterday)->groupBy('day')->take(1)->first();
        $utilization = ($stations_logs->free - $daily->free) / $stations_logs->free;
        SummaryDaily::updateOrCreate(['day' => $yesterday->toDateString()], ['day' => $yesterday->toDateString(), 'bicycles' => $stations_logs->free, 'utilization' => $utilization]);
        $today = Carbon::today();
        $stations = Station::count();
        $stations_logs = StationsLog::selectRaw('SUM(free) as free, DATE(created_at) as day')->whereNotIn('station_id', IGNORED_STATIONS)->whereDate('created_at', '=', $today)->groupBy('created_at')->orderBy('free', 'desc')->take(1)->first();
        $daily = StationsLog::selectRaw('AVG(free) as free, DATE(created_at) as day')->whereNotIn('station_id', IGNORED_STATIONS)->whereDate('created_at', '=', $today)->groupBy('day')->take(1)->first();
        $utilization = ($stations_logs->free - $daily->free) / $stations_logs->free;
        SummaryDaily::updateOrCreate(['day' => $today->toDateString()], ['day' => $today->toDateString(), 'stations' => $stations, 'bicycles' => $stations_logs->free, 'utilization' => $utilization]);
    }
}
