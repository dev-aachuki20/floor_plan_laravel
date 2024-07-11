<?php

namespace App\DataTables;

use App\Models\User;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Support\Str;

class CompanyDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */

    private $authUser;

    public function __construct()
    {
        $this->authUser = auth()->user();
    }


    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query->with(['company'])->select('users.*')))
            ->addIndexColumn()

            ->editColumn('created_at', function($record) {
                return $record->created_at->format('d-m-Y');
            })

            ->editColumn('name', function($record){
                return $record->name ? ucwords($record->name) : '';
            })

            ->editColumn('email', function($record){
                return $record->email ?? '';
            })

            ->editColumn('company.company_name', function($record){
                return $record->company ? ucwords($record->company->company_name) : '';
            })
            
            ->addColumn('action', function($record){
                $actionHtml = '';
               if (Gate::check('company_view')) {
                    $actionHtml .= '<a href="'.route('admin.companies.show',$record->uuid).'" class="btn btn-outline-info btn-sm" title="Show"> <i class="ri-eye-line"></i> </a>';

                }
                if (Gate::check('company_edit')) {
                    $actionHtml .= '<a href="'.route('admin.companies.edit',$record->uuid).'" class="btn btn-outline-success btn-sm" title="Edit"><i class="ri-edit-2-line"></i></a>';
                }
                if (Gate::check('company_delete')) {
                    
                    $actionHtml .= '<button type="button" class="btn btn-outline-danger btn-sm deleteCompanyBtn" data-href="'.route('admin.companies.destroy', $record->uuid).'" title="Delete"><i class="ri-delete-bin-line"></i></button>';

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
    public function query(User $model): QueryBuilder
    {         
        return $model->whereHas('roles',function($query){
            $query->where('id',config('constant.roles.company'));
        })->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {

        $orderByColumn = 4;
        
        return $this->builder()
                    ->setTableId('company-table')
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
        // $columns[] = Column::make('company_logo')->title(trans('cruds.staff.fields.staff_image'))->titleAttr(trans('cruds.staff.fields.staff_image'))->searchable(false)->orderable(false);

      
        $columns[] = Column::make('company.company_name')->title('Company Name');
        $columns[] = Column::make('name')->title('Name');
        $columns[] = Column::make('email')->title('Email');
        $columns[] = Column::make('created_at')->title('Created At');
       
        $columns[] = Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(60)->addClass('text-center action-col');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Companies_' . date('YmdHis');
    }
}
