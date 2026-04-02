<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly SlugService $slugs
    ) {}

    public function index(): View
    {
        $products = Product::query()->with('category')->latest()->paginate(30);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190'],
            'short_description' => ['nullable', 'string', 'max:512'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'is_bestseller' => ['nullable', 'boolean'],
            'sku' => ['nullable', 'string', 'max:255'],
            'variant_title' => ['nullable', 'string', 'max:255'],
            'variant_sku' => ['nullable', 'string', 'max:64'],
            'price_retail' => ['required', 'numeric', 'min:0'],
            'price_reseller' => ['nullable', 'numeric', 'min:0'],
            'price_bulk' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'track_inventory' => ['nullable', 'boolean'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'images.*' => ['nullable', 'image', 'max:5120'],
        ]);

        $base = $request->filled('slug') ? (string) $data['slug'] : (string) $data['name'];
        $slug = $this->slugs->forProduct($base);

        $tags = array_values(array_filter(array_map('trim', explode(',', (string) ($data['tags'] ?? '')))));

        DB::transaction(function () use ($request, $data, $slug, $tags): void {
            $product = Product::query()->create([
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'slug' => $slug,
                'sku' => $data['sku'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'brand' => $data['brand'] ?? null,
                'tags' => $tags ?: null,
                'status' => $data['status'],
                'is_featured' => $request->boolean('is_featured'),
                'is_bestseller' => $request->boolean('is_bestseller'),
            ]);

            $product->variants()->create([
                'title' => $data['variant_title'] ?: 'Default',
                'sku' => $data['variant_sku'] ?? null,
                'price_retail' => $data['price_retail'],
                'price_reseller' => $data['price_reseller'] ?? null,
                'price_bulk' => $data['price_bulk'] ?? null,
                'compare_at_price' => $data['compare_at_price'] ?? null,
                'stock_qty' => $data['stock_qty'],
                'track_inventory' => $request->boolean('track_inventory', true),
                'weight_grams' => $data['weight_grams'] ?? null,
                'is_active' => true,
            ]);

            foreach ($request->file('images', []) ?: [] as $i => $file) {
                if (! $file) {
                    continue;
                }
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'alt_text' => $data['name'],
                    'sort_order' => $i,
                    'is_primary' => $i === 0,
                ]);
            }
        });

        return redirect()->route('admin.products.index')->with('status', __('Product created.'));
    }

    public function edit(Product $product): View
    {
        $product->load(['variants', 'images']);
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $variantsInput = $request->input('variants', []);
        foreach ($variantsInput as $i => $row) {
            if (array_key_exists('id', $row) && $row['id'] === '') {
                unset($variantsInput[$i]['id']);
            }
        }
        $request->merge(['variants' => $variantsInput]);

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190'],
            'short_description' => ['nullable', 'string', 'max:512'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'is_bestseller' => ['nullable', 'boolean'],
            'sku' => ['nullable', 'string', 'max:255'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.title' => ['required', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:64'],
            'variants.*.price_retail' => ['required', 'numeric', 'min:0'],
            'variants.*.price_reseller' => ['nullable', 'numeric', 'min:0'],
            'variants.*.price_bulk' => ['nullable', 'numeric', 'min:0'],
            'variants.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_qty' => ['required', 'integer', 'min:0'],
            'variants.*.track_inventory' => ['nullable', 'boolean'],
            'variants.*.weight_grams' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer', 'exists:product_images,id'],
            'images.*' => ['nullable', 'image', 'max:5120'],
        ]);

        $base = $request->filled('slug') ? (string) $data['slug'] : (string) $data['name'];
        $slug = $this->slugs->forProduct($base, $product->id);

        $tags = array_values(array_filter(array_map('trim', explode(',', (string) ($data['tags'] ?? '')))));

        DB::transaction(function () use ($request, $data, $product, $slug, $tags): void {
            $product->update([
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'slug' => $slug,
                'sku' => $data['sku'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'brand' => $data['brand'] ?? null,
                'tags' => $tags ?: null,
                'status' => $data['status'],
                'is_featured' => $request->boolean('is_featured'),
                'is_bestseller' => $request->boolean('is_bestseller'),
            ]);

            $existingVariantIds = [];
            foreach ($data['variants'] as $row) {
                $payload = [
                    'title' => $row['title'],
                    'sku' => $row['sku'] ?? null,
                    'price_retail' => $row['price_retail'],
                    'price_reseller' => $row['price_reseller'] ?? null,
                    'price_bulk' => $row['price_bulk'] ?? null,
                    'compare_at_price' => $row['compare_at_price'] ?? null,
                    'stock_qty' => $row['stock_qty'],
                    'track_inventory' => (bool) ($row['track_inventory'] ?? true),
                    'weight_grams' => $row['weight_grams'] ?? null,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ];

                if (! empty($row['id'])) {
                    $variant = ProductVariant::query()->where('product_id', $product->id)->whereKey($row['id'])->firstOrFail();
                    $variant->update($payload);
                    $existingVariantIds[] = $variant->id;
                } else {
                    $created = $product->variants()->create($payload);
                    $existingVariantIds[] = $created->id;
                }
            }

            ProductVariant::query()
                ->where('product_id', $product->id)
                ->whereNotIn('id', $existingVariantIds)
                ->delete();

            foreach ($request->input('remove_image_ids', []) as $imageId) {
                $img = ProductImage::query()->where('product_id', $product->id)->whereKey($imageId)->first();
                if ($img) {
                    Storage::disk('public')->delete($img->path);
                    $img->delete();
                }
            }

            $start = (int) $product->images()->max('sort_order') + 1;
            foreach ($request->file('images', []) ?: [] as $i => $file) {
                if (! $file) {
                    continue;
                }
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'alt_text' => $data['name'],
                    'sort_order' => $start + $i,
                    'is_primary' => $product->images()->count() === 0 && $i === 0,
                ]);
            }

            if (! $product->images()->where('is_primary', true)->exists() && $product->images()->exists()) {
                $first = $product->images()->orderBy('sort_order')->first();
                $product->images()->update(['is_primary' => false]);
                $first?->update(['is_primary' => true]);
            }
        });

        return redirect()->route('admin.products.index')->with('status', __('Product updated.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product): void {
            $product->load('images');
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->path);
            }
            $product->delete();
        });

        return redirect()->route('admin.products.index')->with('status', __('Product deleted.'));
    }
}
