<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Agenda;
use App\Models\Informasi;
use App\Models\Galeri;
use App\Models\Category;

class PublicController extends Controller
{
    public function agenda()
    {
        // Cache agenda data for 1 hour
        $agendas = Cache::store('query')->remember('public_agendas', 3600, function () {
            return Agenda::where('status', true)
                        ->orderBy('date', 'asc')
                        ->paginate(12);
        });
        
        return view('agenda', compact('agendas'));
    }

    public function informasi()
    {
        // Cache informasi data for 1 hour
        $informasis = Cache::store('query')->remember('public_informasis', 3600, function () {
            return Informasi::where('status', true)
                           ->orderBy('published_at', 'desc')
                           ->paginate(12);
        });
        return view('informasi', compact('informasis'));
    }

    public function galeri()
    {
        // Cache categories for 24 hours
        $categories = Cache::store('query')->remember('gallery_categories', 86400, function () {
            return Category::select('id', 'nama', 'slug')->get();
        });
        
        $galeris = Galeri::with(['category'])
                         ->withCount(['likes', 'comments'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(6);
        return view('galeri', compact('galeris', 'categories'));
    }

}
