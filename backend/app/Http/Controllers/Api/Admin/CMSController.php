<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use App\Models\Banner;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CMSController extends Controller
{
    
    public function getSections(Request $request): JsonResponse
    {
        try {
            $sections = HomepageSection::orderBy('display_order')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $sections,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get homepage sections', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load homepage sections.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getSection(HomepageSection $section): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $section,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load section.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function saveSection(Request $request, ?HomepageSection $section = null): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'slug' => ['nullable', 'string', 'max:100'],
                'title' => ['nullable', 'string', 'max:255'],
                'subtitle' => ['nullable', 'string'],
                'content' => ['nullable', 'string'],
                'type' => ['required', 'in:hero,featured,categories,collections,new_arrivals,bestsellers,sale,testimonials,newsletter,banner,custom'],
                'settings' => ['nullable', 'array'],
                'product_ids' => ['nullable', 'array'],
                'product_ids.*' => ['integer', 'exists:products,id'],
                'display_order' => ['nullable', 'integer', 'min:0'],
                'is_visible' => ['nullable', 'boolean'],
                'background_color' => ['nullable', 'string', 'max:7'],
                'background_image' => ['nullable', 'string'],
            ]);

            if (!$section) {
                
                $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

                $baseSlug = $validated['slug'];
                $counter = 1;
                while (HomepageSection::where('slug', $validated['slug'])->exists()) {
                    $validated['slug'] = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $section = HomepageSection::create($validated);
                
                ActivityLog::log(
                    'create',
                    'cms',
                    "Created homepage section: {$section->name}",
                    $section
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Homepage section created successfully.',
                    'data' => $section,
                ], 201);
            } else {
                
                $oldValues = $section->toArray();
                $section->update($validated);
                
                ActivityLog::log(
                    'update',
                    'cms',
                    "Updated homepage section: {$section->name}",
                    $section,
                    $oldValues,
                    $section->toArray()
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Homepage section updated successfully.',
                    'data' => $section->fresh(),
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to save homepage section', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save homepage section.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function deleteSection(HomepageSection $section): JsonResponse
    {
        try {
            $sectionName = $section->name;
            $section->delete();

            ActivityLog::log(
                'delete',
                'cms',
                "Deleted homepage section: {$sectionName}",
                $section
            );

            return response()->json([
                'success' => true,
                'message' => 'Homepage section deleted successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete homepage section', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete homepage section.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getBanners(Request $request): JsonResponse
    {
        try {
            $position = $request->input('position');
            
            $query = Banner::query();
            
            if ($position) {
                $query->where('position', $position);
            }

            $banners = $query->orderBy('display_order')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $banners,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get banners', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load banners.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getBanner(Banner $banner): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $banner,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load banner.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function saveBanner(Request $request, ?Banner $banner = null): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'title' => ['nullable', 'string', 'max:255'],
                'subtitle' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
                'desktop_image' => ['required', 'string'],
                'mobile_image' => ['nullable', 'string'],
                'alt_text' => ['nullable', 'string', 'max:255'],
                'link_url' => ['nullable', 'string'],
                'link_text' => ['nullable', 'string', 'max:100'],
                'button_text' => ['nullable', 'string', 'max:100'],
                'open_in_new_tab' => ['nullable', 'boolean'],
                'position' => ['required', 'in:home_hero,home_middle,home_bottom,category_top,product_top,cart_top,checkout_top,sidebar,popup'],
                'display_order' => ['nullable', 'integer', 'min:0'],
                'is_visible' => ['nullable', 'boolean'],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date', 'after:starts_at'],
            ]);

            if (isset($validated['link_text']) && !isset($validated['button_text'])) {
                $validated['button_text'] = $validated['link_text'];
            }

            if (!$banner) {
                $banner = Banner::create($validated);
                
                ActivityLog::log(
                    'create',
                    'cms',
                    "Created banner: {$banner->name}",
                    $banner
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Banner created successfully.',
                    'data' => $banner,
                ], 201);
            } else {
                $oldValues = $banner->toArray();
                $banner->update($validated);
                
                ActivityLog::log(
                    'update',
                    'cms',
                    "Updated banner: {$banner->name}",
                    $banner,
                    $oldValues,
                    $banner->toArray()
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Banner updated successfully.',
                    'data' => $banner->fresh(),
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to save banner', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save banner.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function deleteBanner(Banner $banner): JsonResponse
    {
        try {
            $bannerName = $banner->name;
            $banner->delete();

            ActivityLog::log(
                'delete',
                'cms',
                "Deleted banner: {$bannerName}",
                $banner
            );

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete banner', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
