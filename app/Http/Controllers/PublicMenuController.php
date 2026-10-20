<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

/**
 * Digital menu that customers open by scanning a QR code (FR-41). No login needed.
 */
class PublicMenuController extends Controller
{
    public function index(): View
    {
        $categories = Category::with(['menuItems' => fn ($q) => $q->where('is_available', true)->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($c) => $c->menuItems->isNotEmpty());

        return view('public.menu', ['categories' => $categories]);
    }

    /** Admin page with the QR code to print and place on the tables. */
    public function qr(): View
    {
        return view('public.qr', ['menuUrl' => route('public.menu')]);
    }
}
