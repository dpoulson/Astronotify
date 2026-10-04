<?php

namespace App\Http\Controllers;

use App\Models\ISSTransit;
use App\Models\StargazingSpot;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap for SEO crawlers.
     */
    public function index(): Response
    {
        $content = Cache::remember('sitemap_xml', 3600, function () {
            $urls = [];

            // Static high-level marketing & informational routes
            $urls[] = [
                'loc' => url('/'),
                'lastmod' => now()->startOfDay()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ];

            $urls[] = [
                'loc' => route('spots.index'),
                'lastmod' => now()->startOfDay()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ];

            $urls[] = [
                'loc' => route('about'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];

            $urls[] = [
                'loc' => route('faq'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];

            $urls[] = [
                'loc' => route('privacy'),
                'lastmod' => now()->startOfMonth()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];

            // Curated active stargazing spots directory
            $spots = StargazingSpot::active()
                ->select(['slug', 'updated_at'])
                ->orderBy('name')
                ->get();

            foreach ($spots as $spot) {
                $urls[] = [
                    'loc' => route('spots.show', $spot->slug),
                    'lastmod' => ($spot->updated_at ?? now())->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }

            // Upcoming public transit passes
            $transits = ISSTransit::where('time', '>=', now())
                ->whereNotNull('public_token')
                ->select(['public_token', 'updated_at'])
                ->orderBy('time')
                ->take(100)
                ->get();

            foreach ($transits as $transit) {
                $urls[] = [
                    'loc' => route('transit.show', $transit->public_token),
                    'lastmod' => ($transit->updated_at ?? now())->toAtomString(),
                    'changefreq' => 'daily',
                    'priority' => '0.6',
                ];
            }

            return view('sitemap', compact('urls'))->render();
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
