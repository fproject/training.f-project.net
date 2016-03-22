<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

/**
 *
 * The Pagination class represents the model for RPC result with pagination.
 *
 * @author Bui Sy Nguyen
 *
 */
class PaginationResult
{
    /**
     * Map the ActionScript class that has alias 'FAppContextData' to this VO class:
     */
    public $_explicitType = 'FPaginationResult';

    /**
     * @var array $items Current page items
     */
    public $items;

    /**
     * @var mixed $links The <code>links</code> object contains URL to current page, next page and last page,
     * which is in a format like following example:
     * <pre>
     *	{
     *		self : {"href":"http://rest.f-project.net/projects?page=2&per-page=5"},
     * 		next : {"href":"http://rest.f-project.net/projects?page=3&per-page=5"},
     * 		last : {"href":"http://rest.f-project.net/projects?page=6&per-page=5"}
     *	}<pre>
     */
    public $links;

    /**
     * @var mixed $meta The <code>meta</code> object contains totalCount, pageCount, currentPage and perPage information,
     * which is in a format like following example:
     * <pre>
     *	{
     *		totalCount:29, pageCount:6, currentPage:2, perPage:5
     *	}<pre>
     */
    public $meta;

    public function __construct($currentPage, $perPage, $items, $totalCount)
    {
        $this->meta = new stdClass();
        $this->meta->currentPage = $currentPage;
        $this->meta->perPage = $perPage;
        $this->meta->totalCount = $totalCount;
        $this->meta->pageCount = ceil($totalCount/$perPage);
        $this->items = $items;
    }
} 