<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use Intervention\Image\Image;
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::OrderBy('created_at', 'DESC')->paginate(10);
        return view('products', compact('products'));
    }
    public function store(Request $request)
    {
        //VALIDASI DATA YANG DITERIMA
        $this->validate($request, [
            'name' => 'required|string|max:80',
            'image' => 'required|image|mimes:jpg,jpeg,png'
        ]);

        //JIKA FILE GAMBARNYA ADA
        if ($request->hasFile('image')) {
            $file = $request->file('image');  //GET FILE GAMBARNYA
            $filenameWithoutEx = Str::slug($request->name) . '-' . time(); //GENERATE NAMA FILE TANPA EXTENSION
            $filename = $filenameWithoutEx . '.' . $file->getClientOriginalExtension(); //GENERATE NAMA FILE DENGAN EXTENSION
            $file->storeAs('public/products', $filename); //SIMPAN FILE ORIGINAL YANG BELUM BERISI WATERMARK KE DALAM STORAGE/APP/PUBLIC/PRODUCTS

            $img = Image::make(storage_path('app/public/products/' . $filename));  //GET FILE YANG SUDAH DISIMPAN
            //KEMUDIAN KITA SISIPKAN WATERMARK DENGAN TEXT DAENGWEB.ID
            //X = 200, Y = 150. SILAHKAN DISESUAIKAN UNTUK POSISINYA
            $img->text('Watermark', 200, 150, function ($font) {
                $font->file(public_path('milkyroad.ttf'));   //LOAD FONT-NYA JIKA ADA, SILAHKAN DOWNLOAD SENDIRI
                $font->size(50);
                $font->color('#e74c3c');
                $font->align('center');
                $font->valign('middle');
                $font->angle(30);
            });
            $filenameWatermark = $filenameWithoutEx . '_watermark.' . $file->getClientOriginalExtension(); //GENERATE NAMA FILE YANG SUDAH BERISI WATERMARK
            $img->save(storage_path('app/public/products/' . $filenameWatermark)); //DAN SIMPAN JUGA KE DALAM FOLDER YG SAMA

            //SIMPAN INFORMASI PRODUKNYA KE DALAM TABLE PRODUCTS
            Product::create([
                'name' => $request->name,
                'original_image' => $filename,
                'image' => $filenameWatermark
            ]);
            return redirect()->back()->with(['success' => 'Produk Berhasil Di Unggah']);
        }
        return redirect()->back()->with(['error' => 'File Gambar Tidak Ditemukan']);
    }
}
