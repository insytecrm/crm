<?php

namespace App\Support;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class LeadDrawerRedirect
{
    public static function to(Lead $lead, ?string $status = null): RedirectResponse
    {
        $redirect = redirect()->to(self::url($lead));

        if ($status !== null) {
            $redirect->with('status', $status);
        }

        return $redirect;
    }

    public static function url(Lead $lead): string
    {
        $previous = url()->previous();
        $path = parse_url($previous, PHP_URL_PATH) ?? '';

        if (! self::shouldReopenDrawer($path, $previous)) {
            return $previous;
        }

        if (preg_match('#/leads/\d+#', $path) === 1) {
            $previous = route('tenant.leads.index');
        }

        return self::appendLeadQuery($previous, $lead->id);
    }

    private static function shouldReopenDrawer(string $path, string $url): bool
    {
        if (preg_match('#/leads/?$#', $path) === 1) {
            return true;
        }

        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);

        return isset($query['lead']);
    }

    public static function appendLeadQuery(string $url, int $leadId): string
    {
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['path'])) {
            return route('tenant.leads.index', ['lead' => $leadId]);
        }

        parse_str($parts['query'] ?? '', $query);
        $query['lead'] = $leadId;

        $scheme = isset($parts['scheme']) ? $parts['scheme'].'://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'];
        $queryString = http_build_query($query);

        return $scheme.$host.$port.$path.($queryString !== '' ? '?'.$queryString : '');
    }
}
