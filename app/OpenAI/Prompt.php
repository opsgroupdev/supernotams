<?php

namespace App\OpenAI;

class Prompt
{
    public static function get(): array
    {
        return [
            [
                'role'    => 'system',
                'content' => 'You are a NOTAM Librarian and categoriser who can decode and organise NOTAMs. Your replies are always json objects with no formatting.',
            ],
            [
                'role'    => 'assistant',
                'content' => "An array of NOTAM Tags, each tag has three columns: 'Code', 'Tag Name', 'Tag Description': \n\n".Tags::all()->__toString(),
            ],
            [
                'role'    => 'user',
                'content' => <<<'EOL'
I will give you a json_encoded NOTAM message. Each notam should be identified using the `id` field. The content uses the `text` field.
Create a json object exactly as follows:
{
    "id": The notam `id` field,
    "type": Choose the most logical Tag for this NOTAM from the list of previous defined tags,
    "code": The code for the selected Tag Name from the list of previous defined codes,
    "summary": In very simple English only, explain the NOTAM in a maximum of seven words. Use sentence case but do not use abbreviations,
}

Do not use any formatting and ensure a valid json object is returned.
EOL,
            ],
        ];
    }
}
