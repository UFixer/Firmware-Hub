<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'parent_id',
        'level',
        'path',
        'children_count',
        'icon',
        'image_url',
        'banner_url',
        'thumbnail_url',
        'color_code',
        'is_active',
        'is_featured',
        'show_in_menu',
        'show_in_homepage',
        'sort_order',
        'product_count',
        'min_price',
        'max_price',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'schema_markup',
        'category_type',
        'attributes',
        'filters',
        'specifications',
        'commission_rate',
        'flat_fee',
        'allow_discount',
        'max_discount_percent',
        'requires_auth',
        'visibility',
        'allowed_user_roles',
        'layout_type',
        'products_per_page',
        'default_sort',
        'custom_fields',
        'admin_notes',
        'created_by',
        'updated_by'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'parent_id' => 'integer',
        'level' => 'integer',
        'children_count' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_homepage' => 'boolean',
        'requires_auth' => 'boolean',
        'allow_discount' => 'boolean',
        'sort_order' => 'integer',
        'product_count' => 'integer',
        'products_per_page' => 'integer',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'flat_fee' => 'decimal:2',
        'max_discount_percent' => 'decimal:2',
        'schema_markup' => 'array',
        'attributes' => 'array',
        'filters' => 'array',
        'specifications' => 'array',
        'allowed_user_roles' => 'array',
        'custom_fields' => 'array'
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'admin_notes'
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Generate slug on creation
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = static::generateSlug($category->name);
            }
            
            // Set level based on parent
            if ($category->parent_id) {
                $parent = static::find($category->parent_id);
                $category->level = $parent ? $parent->level + 1 : 0;
                $category->path = $parent ? $parent->path . '/' . $category->parent_id : $category->parent_id;
            } else {
                $category->level = 0;
                $category->path = null;
            }
        });
        
        // Update parent's children count
        static::created(function ($category) {
            if ($category->parent_id) {
                static::where('id', $category->parent_id)->increment('children_count');
            }
        });
        
        // Update counts on deletion
        static::deleting(function ($category) {
            if ($category->parent_id) {
                static::where('id', $category->parent_id)->decrement('children_count');
            }
        });
        
        // Update path for children when parent changes
        static::updating(function ($category) {
            if ($category->isDirty('parent_id')) {
                $oldPath = $category->getOriginal('path');
                $newParent = static::find($category->parent_id);
                
                if ($newParent) {
                    $category->level = $newParent->level + 1;
                    $category->path = $newParent->path ? $newParent->path . '/' . $newParent->id : (string)$newParent->id;
                } else {
                    $category->level = 0;
                    $category->path = null;
                }
                
                // Update all children paths
                if ($oldPath !== null) {
                    static::updateChildrenPaths($category->id, $oldPath, $category->path);
                }
            }
        });
    }
    
    /**
     * Generate a unique slug.
     *
     * @param string $name
     * @return string
     */
    protected static function generateSlug($name)
    {
        $slug = Str::slug($name);
        $count = static::where('slug', 'LIKE', "{$slug}%")->count();
        
        return $count ? "{$slug}-{$count}" : $slug;
    }
    
    /**
     * Update paths for all children categories.
     *
     * @param int $parentId
     * @param string $oldPath
     * @param string|null $newPath
     */
    protected static function updateChildrenPaths($parentId, $oldPath, $newPath)
    {
        $children = static::where('parent_id', $parentId)->get();
        
        foreach ($children as $child) {
            $childOldPath = $child->path;
            $child->path = $newPath ? $newPath . '/' . $parentId : (string)$parentId;
            $child->level = $newPath ? substr_count($newPath, '/') + 2 : 1;
            $child->save();
            
            // Recursively update grandchildren
            if ($child->children_count > 0) {
                static::updateChildrenPaths($child->id, $childOldPath, $child->path);
            }
        }
    }
    
    /**
     * Scope for active categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for featured categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
                    ->orderBy('sort_order');
    }
    
    /**
     * Scope for menu categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true)
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }
    
    /**
     * Scope for root categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
    
    /**
     * Get the full path with names.
     *
     * @return string
     */
    public function getFullPathAttribute()
    {
        if (!$this->path) {
            return $this->name;
        }
        
        $ids = explode('/', $this->path);
        $categories = static::whereIn('id', $ids)->pluck('name', 'id');
        
        $names = [];
        foreach ($ids as $id) {
            if (isset($categories[$id])) {
                $names[] = $categories[$id];
            }
        }
        $names[] = $this->name;
        
        return implode(' > ', $names);
    }
    
    /**
     * Get breadcrumb data.
     *
     * @return array
     */
    public function getBreadcrumbsAttribute()
    {
        $breadcrumbs = [];
        
        if ($this->path) {
            $ids = explode('/', $this->path);
            $categories = static::whereIn('id', $ids)
                ->select('id', 'name', 'slug')
                ->get()
                ->keyBy('id');
            
            foreach ($ids as $id) {
                if (isset($categories[$id])) {
                    $breadcrumbs[] = $categories[$id];
                }
            }
        }
        
        $breadcrumbs[] = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug
        ];
        
        return $breadcrumbs;
    }
    
    /**
     * Check if category has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->children_count > 0;
    }
    
    /**
     * Check if category is child of another category.
     *
     * @param int $parentId
     * @return bool
     */
    public function isChildOf($parentId)
    {
        if (!$this->path) {
            return false;
        }
        
        $pathIds = explode('/', $this->path);
        return in_array($parentId, $pathIds);
    }
    
    /**
     * Update product count and price range.
     *
     * @return void
     */
    public function updateProductStats()
    {
        $stats = $this->files()
            ->where('status', 'active')
            ->where('is_public', true)
            ->selectRaw('COUNT(*) as count, MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        
        $this->update([
            'product_count' => $stats->count ?? 0,
            'min_price' => $stats->min_price ?? 0,
            'max_price' => $stats->max_price ?? 0
        ]);
    }
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    
    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order');
    }
    
    /**
     * Get all descendant categories.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }
    
    /**
     * Get the files in this category.
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }
    
    /**
     * Get active files in this category.
     */
    public function activeFiles()
    {
        return $this->hasMany(File::class)
            ->where('status', 'active')
            ->where('is_public', true);
    }
    
    /**
     * Get featured files in this category.
     */
    public function featuredFiles()
    {
        return $this->hasMany(File::class)
            ->where('status', 'active')
            ->where('is_featured', true)
            ->orderBy('sort_order');
    }
    
    /**
     * Get the products in this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    /**
     * Get the user who created this category.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user who last updated this category.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Get all files including from child categories.
     */
    public function allFiles()
    {
        $categoryIds = [$this->id];
        
        // Get all descendant category IDs
        $descendants = $this->descendants()->get();
        foreach ($descendants as $descendant) {
            $categoryIds[] = $descendant->id;
        }
        
        return File::whereIn('category_id', $categoryIds);
    }
}