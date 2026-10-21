<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * The public website: home page and contact form.
 */
class SiteController extends Controller
{
    public function home(): View
    {
        return view('site.home', [
            'popular' => $this->popularDishes(),
            'categories' => Category::whereHas('menuItems', fn ($q) => $q->where('is_available', true))
                ->withCount(['menuItems' => fn ($q) => $q->where('is_available', true)])->orderBy('name')->get(),
            'roomTypes' => RoomType::withCount('rooms')->orderBy('price_per_night')->get(),
            'stats' => [
                'tables' => RestaurantTable::count(),
                'rooms' => Room::count(),
                'dishes' => MenuItem::where('is_available', true)->count(),
            ],
        ]);
    }

    /** Contact form: saved for the Admin to read in Messages. */
    public function contact(Request $request): RedirectResponse
    {
        // Hidden field that people never fill in; simple robots do
        if (filled($request->input('website'))) {
            return back()->with('success', 'Thank you! We will reply soon.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable', 'regex:/^[0-9 +\-]{9,20}$/'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        ContactMessage::create($data + ['ip_address' => $request->ip()]);

        return redirect(route('home').'#contact')->with('success', 'Thank you, '.$data['name'].'. Your message has been sent. We will reply soon.');
    }

    /** Best sellers of the last 60 days (with photos first); newest dishes if there are no sales yet. */
    private function popularDishes()
    {
        $sold = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'closed')
            ->where('order_items.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', now()->subDays(60))
            ->groupBy('order_items.menu_item_id')
            ->selectRaw('order_items.menu_item_id, SUM(order_items.quantity) as qty')
            ->pluck('qty', 'menu_item_id');

        return MenuItem::with('category')
            ->where('is_available', true)
            ->get()
            ->sortByDesc(fn ($item) => [$item->photo ? 1 : 0, (int) ($sold[$item->id] ?? 0), $item->id])
            ->take(6)
            ->values();
    }
}
