<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/products",
        summary: "Listar todos los productos",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de productos",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Product")
                )
            ),
        ]
    )]
    public function index()
    {
        return ProductResource::collection(Product::with('category')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/products",
        summary: "Crear un nuevo producto",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Product")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Producto creado exitosamente",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return new ProductResource($product->load('category'));
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/products/{id}",
        summary: "Obtener un producto por ID",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Producto encontrado",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(response: 404, description: "Producto no encontrado"),
        ]
    )]
    public function show(Product $product)
    {
        return new ProductResource($product->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/products/{id}",
        summary: "Actualizar un producto existente",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Product")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Producto actualizado",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(response: 404, description: "Producto no encontrado"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    public function update(StoreProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return new ProductResource($product->load('category'));
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/products/{id}",
        summary: "Eliminar un producto",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 204, description: "Producto eliminado"),
            new OA\Response(response: 404, description: "Producto no encontrado"),
        ]
    )]
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }
}