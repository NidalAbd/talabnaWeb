<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Spatie\TranslationLoader\LanguageLine;
use App\Http\Controllers\Controller;

class TranslationController extends Controller
{
    public function index()
    {
        $translations = LanguageLine::paginate(20);
        return view('admin.translations.index', compact('translations'));
    }

    public function create()
    {
        return view('admin.translations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'group' => 'required|string',
            'key' => 'required|string',
            'text_en' => 'required|string',
            'text_ar' => 'nullable|string',
        ]);
        LanguageLine::create([
            'group' => $data['group'],
            'key' => $data['key'],
            'text' => [
                'en' => $data['text_en'],
                'ar' => $data['text_ar'],
            ],
        ]);
        return redirect()->route('translations.index')->with('success', 'Translation added!');
    }

    public function edit(LanguageLine $translation)
    {
        return view('admin.translations.edit', compact('translation'));
    }

    public function update(Request $request, LanguageLine $translation)
    {
        $data = $request->validate([
            'text_en' => 'required|string',
            'text_ar' => 'nullable|string',
        ]);
        $text = $translation->text;
        $text['en'] = $data['text_en'];
        $text['ar'] = $data['text_ar'];
        $translation->update(['text' => $text]);
        return redirect()->route('translations.index')->with('success', 'Translation updated!');
    }

    public function inlineUpdate(Request $request, LanguageLine $translation)
    {
        $request->validate([
            'lang' => 'required|string',
            'value' => 'nullable|string',
        ]);
        $text = $translation->text;
        $text[$request->lang] = $request->value;
        $translation->text = $text;
        $translation->save();
        return response()->json(['success' => true]);
    }

    public function autoTranslate(Request $request)
    {
        $request->validate([
            'lang' => 'required|string',
        ]);
        \Artisan::call('translations:auto-translate', ['lang' => $request->lang]);
        return response()->json(['message' => 'Auto-translation complete for ' . $request->lang]);
    }
} 