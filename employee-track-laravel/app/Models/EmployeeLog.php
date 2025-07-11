<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'url',
        'title',
        'domain',
        'content',
        'activity_data',
        'form_data',
        'logged_at',
    ];

    protected $casts = [
        'activity_data' => 'array',
        'form_data' => 'array',
        'logged_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    // Scopes
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('logged_at', [$startDate, $endDate]);
    }

    public function scopeWebsiteVisits($query)
    {
        return $query->where('type', 'website_visit');
    }

    public function scopeKeystrokes($query)
    {
        return $query->where('type', 'keystroke');
    }

    public function scopeActivities($query)
    {
        return $query->where('type', 'activity');
    }
}