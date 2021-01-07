<?php

namespace App\Http\Controllers;

use App\Models\BordereauremiseLoc;
use App\Models\User;
use App\Models\Bordereauremise;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\BordereauremiseType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Foundation\Application;

use Exception;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\SearchCollection;
use App\Http\Requests\Bordereauremise\FetchRequest;
use App\Http\Resources\Bordereauremise as BordereauremiseResource;
use App\Repositories\Contracts\IBordereauremiseRepositoryContract;
use Spatie\Permission\Models\Role;

class BordereauremiseController extends Controller
{
    /**
     * @var IBordereauremiseRepositoryContract
     */
    private $repository;

    /**
     * BordereauremiseController constructor.
     *
     * @param IBordereauremiseRepositoryContract $repository [description]
     */
    public function __construct(IBordereauremiseRepositoryContract $repository) {
        $this->repository = $repository;
    }

    public function bordereauremisetest() {
        $user = auth()->user(); //$user = User::where(......, ........)->first(); // Without first() it's a query builder
        //$user = User::where('id', $user->id)->first();
        $userroles = Role::whereIn('id', DB::table('model_has_roles')->where('model_type','App\Models\User')->where('model_id', $user->id)->select(['role_id'])
        )->get()->pluck('id')->toArray();
        $superroleIds = [1];
        //dd($userroles);
        $ids = [1,2,3];
        $cb = function ($query, $userroles)
        {
            $query->whereIn('role_id', $userroles);
        };
        $results = Bordereauremise::whereIn('id', $ids)->with(['workflowexec.currentstep' => $cb])->get();
        dd($results);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|Response|View
     */
    public function index()
    {
        $localisations = BordereauremiseLoc::all();
        $types = BordereauremiseType::all();
        $bordereaux_wf = Workflow::where('model_type', 'App\Models\Bordereauremise')->first();
        $statuts = $bordereaux_wf ? WorkflowStep::where('workflow_id', $bordereaux_wf->id)->orWhereNull('workflow_id')->get() : null;
        return view('bordereauremises.index')
            ->with('perPage', new \Illuminate\Support\Collection(config('system.per_page')))
            ->with('defaultPerPage', config('system.default_per_page'))
            ->with('localisations', $localisations)
            ->with('types', $types)
            ->with('statuts', $statuts)
            ;
    }

    /**
     * Fetch records.
     *
     * @param  FetchRequest     $request [description]
     * @return SearchCollection          [description]
     */
    public function fetch(FetchRequest $request)
    {
        /*$request->replace([
            'search' => "32",
            'order_by' => "numero_transaction:asc"
        ]);*/
        //dd($request->all());
        return new SearchCollection(
            $this->repository->search($request), BordereauremiseResource::class
        );
    }

    public function fetch_old() {
        $bordereauremises_all = Bordereauremise::all();
        $bordereauremises_all->load(['workflowexec','workflowexec.currentstep','workflowexec.currentstep.profile','workflowexec.workflowstatus','workflowexec.workflow']);

        $user = auth()->user();//User::where('id', Auth::user()->id())->first();

        //dd($user->hasRole(['Chef Agence','Admin']));

        $validation = [];

        $bordereauremises_arr = array();
        foreach ($bordereauremises_all as $bord) {

            if ($bord->workflowexec) {
                //$validation[] = [ "hasRole" . $bord->workflowexec->currentstep->profile->name => $user->hasRole([$bord->workflowexec->currentstep->profile->name, 'Admin']) ];
                if ($bord->workflowexec->currentstep) {
                    if ($user->hasRole([$bord->workflowexec->currentstep->profile->name, 'Admin'])) {
                        $bordereauremises_arr[] = $bord;
                    }
                }
            } else {
                $bordereauremises_arr[] = $bord;
            }
        }

        $bordereauremises = Collection::make($bordereauremises_arr);

        //dd("Validation: ", $validation, "Coll: ", $bordereauremises);
        return $bordereauremises_arr;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Bordereauremise $bordereauremise
     * @return Response
     */
    public function show(Bordereauremise $bordereauremise)
    {
        $bordereauremise = Bordereauremise::where('id',$bordereauremise->id)
            ->first()
            ->load(['lignes','workflowexec','workflowexec.currentstep','workflowexec.currentstep.actions','workflowexec.currentstep.actions.type','workflowexec.currentstep.actions.objectfield','workflowexec.currentstep.profile','workflowexec.workflowstatus']);

        $actionvalues = [];
        if ($bordereauremise->workflowexec && $bordereauremise->workflowexec->currentstep) {

            foreach ($bordereauremise->workflowexec->currentstep->actions as $action) {
                $actionvalues[$action->objectfield->db_field_name] = null;
            }
            $actionvalues['setvalue'] = null;
            $actionvalues['motif_rejet'] = null;
        }

        return view('bordereauremises.show', ['bordereauremise' => $bordereauremise, 'actionvalues' => json_encode($actionvalues)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Bordereauremise $bordereauremise
     * @return Response
     */
    public function edit(Bordereauremise $bordereauremise)
    {
        $user = auth()->user();
        $userprofile = $user->roles()->first();

        $exec_step_profile = $bordereauremise->workflowexec->currentstep->profile;

        // récupérer le bon profile utilisateur
        if ($user->hasRole([$exec_step_profile->name])) {
            $userprofile = $exec_step_profile;
        }

        $bordereauremise = Bordereauremise::where('id',$bordereauremise->id)
            ->first()
            ->load(['type','localisation', 'modepaiement', 'lignes', 'lignes.currmodelstep','lignes.currmodelstep.exec','lignes.currmodelstep.exec.currentstep','lignes.currmodelstep.exec.currentstep.profile','lignes.currmodelstep.step']);
            //->load(['type','lignes','localisation','workflowexec']);//,'workflowexec.currentstepactions','workflowexec.currentstep','workflowexec.currentstep.actions','workflowexec.currentstep.actions.type','workflowexec.currentstep.actions.objectfield','workflowexec.currentstep.profile','workflowexec.workflowstatus']);
            //->load(['type','lignes','localisation','workflowexec','workflowexec.currentstep','workflowexec.currentstep.actions','workflowexec.currentstep.actions.type','workflowexec.currentstep.actions.objectfield','workflowexec.currentstep.profile','workflowexec.workflowstatus']);

        $bordereauremise->load(['currmodelstep','currmodelstep.exec','currmodelstep.exec.currentstep','currmodelstep.exec.currentstep.profile','currmodelstep.step','currmodelstep.actions']);

        $hasexecrole = $exec_step_profile ? ( $user->hasRole([$exec_step_profile->name]) ? 1 : 0 ) : 0;

        //dd($bordereauremise);

        //$bordereauremise->load('execactions','execactions.action');

        $actionvalues = [];
        // Check et récupère les currents execactions dans le bordereau
        /*if ($bordereauremise->workflowexec && $bordereauremise->workflowexec->currentstep) {

            $current_step = $bordereauremise->workflowexec->currentstep;

            // Récupération des execactions du bordereau
            foreach ($bordereauremise->execactions as $execaction) {
                if ($current_step->id === $execaction->action->workflow_step_id) {
                    $actionvalues = $this->addToActionValue($actionvalues, $execaction, $execaction->action->objectfield);
                }
            }

            // Récupération des execactions des lignes du bordereau
            foreach ($bordereauremise->lignes as $ligne) {
                foreach ($ligne->execactions as $execaction) {
                    if ($current_step->id === $execaction->action->workflow_step_id) {
                        $actionvalues = $this->addToActionValue($actionvalues, $execaction, $execaction->action->objectfield);
                    }
                }
            }
            //$actionvalues['setvalue'] = null;
            //$actionvalues['motif_rejet'] = null;
        }*/
        //dd($bordereauremise,$actionvalues);
        /*if ($bordereauremise->workflowexec && $bordereauremise->workflowexec->currentstep) {

            foreach ($bordereauremise->workflowexec->currentstepactions as $execaction) {
                $actionvalues = $this->addActionValue($actionvalues, $execaction->model_id, $execaction->action->objectfield, null);
                //$actionvalues[$execaction->action->objectfield->db_field_name] = null;
            }
            $actionvalues['setvalue'] = null;
            $actionvalues['motif_rejet'] = null;
        }*/

        //dd($bordereauremise->workflowexec->currentstepactions,$actionvalues);

        /*if ($bordereauremise->workflowexec && $bordereauremise->workflowexec->currentstep) {

            foreach ($bordereauremise->workflowexec->currentstep->actions as $action) {
                if ($action->objectfield->object->parent) {
                    // si l'action doit être exécutée sur un objet enfant
                    // on doit boucler sur tous les enfants du parent
                    $models = $action->objectfield->object->model_type::where($action->objectfield->object->ref_field, $bordereauremise->workflowexec->model_id)->get();
                    foreach ($models as $model) {
                        $actionvalues = $this->addActionValue($actionvalues,$model->id,$action->objectfield);
                    }
                } else {
                    $actionvalues = $this->addActionValue($actionvalues,$bordereauremise->workflowexec->model_id,$action->objectfield);
                }
            }
            $actionvalues['setvalue'] = null;
            $actionvalues['motif_rejet'] = null;
        }*/

        //dd($actionvalues);

        return view('bordereauremises.edit', ['bordereauremise' => $bordereauremise, 'actionvalues' => json_encode($actionvalues), 'hasexecrole' => $hasexecrole, 'userprofile' => $userprofile]);
    }

    private function addToActionValue($actionvalues, $execaction, $objectfield) {
        $model_added = false;
        for ($i = 0; $i < count($actionvalues); $i++) {
            if ($actionvalues[$i]['model_id'] === $execaction->model_id && $actionvalues[$i]['model_type'] === $objectfield->object->model_type) {
                // Elément existant
                if (! isset($actionvalues[$i]['actions'][$objectfield->db_field_name])) {
                    // le champs n'est pas déjà représenté pour cet objet
                    $actionvalues[$i]['actions'][$objectfield->db_field_name] = $this->actionValuesFieldRow($execaction->id,$objectfield);
                    $model_added = true;
                }
            }
        }
        if (! $model_added) {
            $actionvalues[] = $this->actionValuesRow($execaction->id, $execaction->model_id, $objectfield);
        }

        return $actionvalues;
    }

    private function actionValuesRow($execaction_id, $model_id, $objectfield) {
        $actionvalues_row = [
            'model_id' => $model_id,
            'model_type' => $objectfield->object->model_type,
            'actions' => [
                $objectfield->db_field_name => $this->actionValuesFieldRow($execaction_id,$objectfield),
            ]
        ];

        return $actionvalues_row;
    }

    private function actionValuesFieldRow($execaction_id,$objectfield) {
        return [
            'execaction_id' => $execaction_id,
            'value' => null,
            'valuetype' => [
                'valuetype_string' => $objectfield->valuetype_string,
                'valuetype_integer' => $objectfield->valuetype_integer,
                'valuetype_boolean' => $objectfield->valuetype_boolean,
                'valuetype_datetime' => $objectfield->valuetype_datetime,
                'valuetype_image' => $objectfield->valuetype_image,
            ]
        ];
    }

    private function addActionValue($actionvalues, $model_id, $objectfield, $value = null) {
        $model_added = false;
        for ($i = 0; $i < count($actionvalues); $i++) {
            if ($actionvalues[$i]['model_id'] === $model_id && $actionvalues[$i]['model_type'] === $objectfield->object->model_type) {
                // Elément existant
                if (! isset($actionvalues[$i][$objectfield->db_field_name])) {
                    // le champs n'est pas déjà représenté pour cet objet
                    $actionvalues[$i][$objectfield->db_field_name] = [
                        'value' => $value,
                        'valuetype' => [
                            'valuetype_string' => $objectfield->valuetype_string,
                            'valuetype_integer' => $objectfield->valuetype_integer,
                            'valuetype_boolean' => $objectfield->valuetype_boolean,
                            'valuetype_datetime' => $objectfield->valuetype_datetime,
                            'valuetype_image' => $objectfield->valuetype_image,
                        ]
                    ];
                    $model_added = true;
                }
            }
        }
        if (! $model_added) {
            $actionvalues[] = [
                'model_id' => $model_id,
                'model_type' => $objectfield->object->model_type,
                $objectfield->db_field_name => [
                    'value' => $value,
                    'valuetype' => [
                        'valuetype_string' => $objectfield->valuetype_string,
                        'valuetype_integer' => $objectfield->valuetype_integer,
                        'valuetype_boolean' => $objectfield->valuetype_boolean,
                        'valuetype_datetime' => $objectfield->valuetype_datetime,
                        'valuetype_image' => $objectfield->valuetype_image,
                    ]
                ]
            ];
        }

        return $actionvalues;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Bordereauremise $bordereauremise
     * @return Response
     */
    public function update(Request $request, Bordereauremise $bordereauremise)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Bordereauremise $bordereauremise
     * @return Response
     */
    public function destroy(Bordereauremise $bordereauremise)
    {
        //
    }
}
