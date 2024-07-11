<?php

namespace App\DataTables;

use App\Models\Industry;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Support\Str;

class IndustryDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */

  
    public function __construct()
    {
     
    }


    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            ->editColumn('created_at', function($record) {
                return $record->created_at->format('d-m-Y');
            })

            ->editColumn('name', function($record){
                return $record->name ? ucwords($record->name) : '';
            })

            ->editColumn('slug', function($record){
                return $record->slug ?? '';
            })

          
            ->addColumn('action', function($record){
                $actionHtml = '';
                /*if (Gate::check('industry_view')) {
                    $actionHtml .= '<button type="button" class="btn btn-outline-info btn-sm" title="View"> <i class="ri-eye-line"></i> </button>';
                }*/
                if (Gate::check('industry_edit')) {
                    $actionHtml .= '<a href="'.route('admin.industries.edit',$record->id).'" class="btn btn-outline-success btn-sm" title="Edit"><i class="ri-edit-2-line"></i></a>';
                }
                if (Gate::check('industry_delete')) {
				    $actionHtml .= '<button type="button" class="btn btn-outline-danger btn-sm deleteIndustryBtn" data-href="'.route('admin.industries.destroy', $record->id).'" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                }
                return $actionHtml;
            })
            ->setRowId('id')

            ->filterColumn('created_at', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(created_at,'%d-%m-%Y') like ?", ["%$keyword%"]); //date_format when searching using date
            })

          
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Industry $model): QueryBuilder
    {         
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {

        $orderByColumn = 3;
        
        return $this->builder()
                    ->setTableId('industry-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    // ->dom('Bfrtip')
                    ->orderBy($orderByColumn)                    
                    ->selectStyleSingle()
                    ->lengthMenu([
                        [10, 25, 50, 100, /*-1*/],
                        [10, 25, 50, 100, /*'All'*/]
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [];

        $columns[] = Column::make('DT_RowIndex')->title('#')->orderable(false)->searchable(false);
       
        $columns[] = Column::make('name')->title('Name');
        $columns[] = Column::make('slug')->title('Slug');
        $columns[] = Column::make('created_at')->title('Created At');
       
        $columns[] = Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(60)->addClass('text-center action-col');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Industries_' . date('YmdHis');
    }
}
