<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\View\View;

class IngredientStockController extends Controller
{
    public function index(): View
    {
        $this->assertKitchen();

        $ingredients = Ingredient::query()
            ->orderBy('name')
            ->paginate(20);

        return view('kitchen.ingredients', compact('ingredients'));
    }

    private function assertKitchen(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->usertype, ['kitchen', 'dapur'], true), 403);
    }
}
