<?php

namespace App\Service;

class DataTableParser
{
    /**
     * Reads the DataTables request structure (as an associative array)
     * and returns an array with 'page', 'per_page', 'search_term', and 'sort' fields.
     *
     * @param array $dataTableData The DataTables request data as an associative array.
     * @return array An array containing the parsed DataTables parameters.
     */
    public function parseDataTableRequest(array $dataTableData): array
    {
        $page = 1;
        $perPage = 10;
        $searchTerm = null;
        $sort = null;

        if (isset($dataTableData['start']) && isset($dataTableData['length']) && is_numeric($dataTableData['length']) && $dataTableData['length'] > 0) {
            $perPage = (int) $dataTableData['length'];
            $page = (int) (($dataTableData['start'] / $perPage) + 1);
        }

        if (isset($dataTableData['search']) && isset($dataTableData['search']['value'])) {
            $searchTerm = trim($dataTableData['search']['value']);
        }

        if (isset($dataTableData['order']) && is_array($dataTableData['order']) && count($dataTableData['order']) > 0) {
            $orderItem = $dataTableData['order'][0]; // Typically only one sort at a time
            
            if (isset($orderItem['column']) && isset($orderItem['dir'])) {
                $columnIndex = (int) $orderItem['column'];
                $sortDirection = strtolower($orderItem['dir']) === 'desc' ? 'desc' : 'asc';

                $sortField = $this->mapColumnIndexToApiField($columnIndex);
                if ($sortField) {
                    $sort = "$sortField:$sortDirection";
                }
            }
            
        }

        return [
            'page' => $page,
            'per_page' => $perPage,
            'search_term' => $searchTerm,
            'sort' => $sort,
        ];
    }
    
    private function mapColumnIndexToApiField(int $index): ?string
    {
        // Map the DataTables column index to the Open Brewery DB API field names        
        switch ($index) {
            case 0: // ID
                return 'id';
            case 1: // Name
                return 'name';
            case 2: // Type (brewery_type in API)
                return 'brewery_type';
            case 3: // City
                return 'city';
            case 4: // State
                return 'state';
            case 5: // Website
                return 'website_url';
            default:
                return null;
        }
    }   
}