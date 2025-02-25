<?php

    namespace Modules\Item\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\ResourceCollection;
    use Modules\Item\Models\Category;

    class CategoryCollection extends ResourceCollection
    {
        /**
         * Transform the resource collection into an array.
         *
         * @param Request $request
         *
         * @return array
         */
        public function toArray($request)
        {
            return $this->collection->transform(function (Category $row, $key) {

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'image' => $row->image,
                    'image_url' => ($row->image !== 'imagen-no-disponible.jpg') ? asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'categories'.DIRECTORY_SEPARATOR.$row->image): asset("/logo/{$row->image}"),
                    'created_at' => ($row->created_at) ? $row->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => ($row->updated_at) ? $row->updated_at->format('Y-m-d H:i:s') : null,
                ];
            });

        }

    }
