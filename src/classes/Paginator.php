<?php

namespace CT275\Labs;

class Paginator
{
    public int $totalPages;
    public int $recordOffset;
    public int $recordsPerPage;
    public int $totalRecords;
    public int $currentPage;

    public function __construct(
        int $recordsPerPage,
        int $totalRecords,
        int $currentPage = 1
    ) {
        $this->recordsPerPage = $recordsPerPage;
        $this->totalRecords = $totalRecords;
        $this->totalPages = (int) ceil($totalRecords / $recordsPerPage);

        $this->currentPage = $currentPage < 1 ? 1 : $currentPage;
        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }

        $this->recordOffset = ($this->currentPage - 1) * $recordsPerPage;
    }

    public function getPrevPage(): int|bool
    {
        return $this->currentPage > 1 ? $this->currentPage - 1 : false;
    }

    public function getNextPage(): int|bool
    {
        return $this->currentPage < $this->totalPages
            ? $this->currentPage + 1
            : false;
    }

    public function getPages(int $length = 3): array
    {
        $halfLength = floor($length / 2);
        $pageStart = $this->currentPage - $halfLength;
        $pageEnd = $this->currentPage + $halfLength;

        if ($pageStart < 1) {
            $pageStart = 1;
            $pageEnd = $length;
        }

        if ($pageEnd > $this->totalPages) {
            $pageEnd = $this->totalPages;
            $pageStart = $pageEnd - $length + 1;
            if ($pageStart < 1) {
                $pageStart = 1;
            }
        }

        return range((int) $pageStart, (int) $pageEnd);
    }
}