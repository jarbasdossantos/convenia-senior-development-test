<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Http\Requests\StoreCollaboratorRequest;
use App\Http\Requests\UpdateCollaboratorRequest;
use App\Http\Requests\CollaboratorCsvUploadRequest;
use App\Jobs\ProcessCollaboratorsCsvJob;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Colaboradores",
 *     description="Endpoints de gerenciamento de colaboradores"
 * )
 */
class CollaboratorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/collaborators",
     *     summary="Listar colaboradores",
     *     description="Retorna uma lista paginada de colaboradores do usuário autenticado",
     *     tags={"Colaboradores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de colaboradores",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Collaborator")
     *             ),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="per_page", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $cacheKey = "collaborators_user_{$user->id}_page_{$page}";

        $collaborators = cache()->remember($cacheKey, 300, function () use ($user) {
            return Collaborator::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->paginate(20);
        });

        return response()->json($collaborators);
    }

    /**
     * @OA\Get(
     *     path="/collaborators/{id}",
     *     summary="Buscar colaborador específico",
     *     description="Retorna os detalhes de um colaborador específico",
     *     tags={"Colaboradores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do colaborador",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Colaborador encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Collaborator")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Colaborador não encontrado"
     *     )
     * )
     */
    public function show(Collaborator $collaborator)
    {
        Gate::authorize('view', $collaborator);

        return response()->json($collaborator);
    }

    /**
     * @OA\Post(
     *     path="/collaborators",
     *     summary="Criar novo colaborador",
     *     description="Cria um novo colaborador para o usuário autenticado",
     *     tags={"Colaboradores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "cpf", "city", "state"},
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="cpf", type="string", example="123.456.789-09"),
     *             @OA\Property(property="city", type="string", example="São Paulo"),
     *             @OA\Property(property="state", type="string", example="SP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Colaborador criado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Collaborator")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function store(StoreCollaboratorRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $collaborator = Collaborator::createOrFirst($data);

        $this->clearCache($request->user()->id);

        return response()->json($collaborator, 201);
    }

    /**
     * @OA\Put(
     *     path="/collaborators/{id}",
     *     summary="Atualizar colaborador",
     *     description="Atualiza os dados de um colaborador existente",
     *     tags={"Colaboradores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do colaborador",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="João Silva Atualizado"),
     *             @OA\Property(property="email", type="string", format="email", example="joao.atualizado@example.com"),
     *             @OA\Property(property="cpf", type="string", example="123.456.789-09"),
     *             @OA\Property(property="city", type="string", example="Rio de Janeiro"),
     *             @OA\Property(property="state", type="string", example="RJ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Colaborador atualizado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Collaborator")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Colaborador não encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function update(UpdateCollaboratorRequest $request, Collaborator $collaborator)
    {
        Gate::authorize('update', $collaborator);

        $data = $request->except('user_id');
        $collaborator->update($data);

        $this->clearCache($collaborator->user_id);

        return response()->json($collaborator);
    }

    /**
     * @OA\Delete(
     *     path="/collaborators/{id}",
     *     summary="Excluir colaborador",
     *     description="Remove um colaborador do sistema",
     *     tags={"Colaboradores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do colaborador",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Colaborador excluído com sucesso"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Colaborador não encontrado"
     *     )
     * )
     */
    public function destroy(Collaborator $collaborator)
    {
        Gate::authorize('delete', $collaborator);

        $userId = $collaborator->user_id;
        $collaborator->delete();

        $this->clearCache($userId);

        return response()->json([], 204);
    }

    /**
     * @OA\Post(
     *     path="/collaborators/import-csv",
     *     summary="Importar colaboradores via CSV",
     *     description="Importa colaboradores a partir de um arquivo CSV",
     *     tags={"Colaboradores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Arquivo CSV com os dados dos colaboradores"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Processamento do arquivo CSV iniciado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Processamento do arquivo CSV iniciado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao processar arquivo")
     *         )
     *     )
     * )
     */
    public function importCsv(CollaboratorCsvUploadRequest $request)
    {
        try {
            $user = $request->user();
            $path = $request->file('file')->store('uploads/collaborators');

            ProcessCollaboratorsCsvJob::dispatch($path, $user->id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Processamento do arquivo CSV iniciado.'], 202);
    }

    private function clearCache(int $userId): void
    {
        for ($page = 1; $page <= 10; $page++) {
            $cacheKey = "collaborators_user_{$userId}_page_{$page}";
            cache()->forget($cacheKey);
        }
    }
}
