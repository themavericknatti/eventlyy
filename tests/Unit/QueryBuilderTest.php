<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ace\QueryBuilder;
use Ace\Model;

class QueryDummyModel extends Model
{
    public static function tableName(): string { return 'articles'; }
    public function primaryKey(): string { return 'id'; }
    public function rules(): array { return []; }
}

class QueryBuilderTest extends TestCase
{
    public function test_query_builder_compiles_clauses_without_exception(): void
    {
        $qb = QueryDummyModel::query()
            ->select('id', 'title', 'status')
            ->where('status', 'published')
            ->orWhere('views', 100, '>')
            ->whereIn('category_id', [1, 2, 5])
            ->whereNull('deleted_at')
            ->whereNotNull('published_at')
            ->whereBetween('likes', 10, 500)
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->offset(20);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
    }
}
