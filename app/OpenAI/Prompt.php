<?php

namespace App\OpenAI;

class Prompt
{
    public static function get(): array
    {
        return [
            [
                'role' => 'user',
                'content' => "An array of NOTAM Tags, each tag has three columns: 'Tag Code', 'Tag Name', 'Tag Description': \n\n".Tags::all()->__toString(),
            ],
            [
                'role' => 'user',
                'content' => <<<'EOL'
You are a NOTAM Librarian. I will give you a number of NOTAM messages. Each start with an identity key then a colon.
Create a JSON array object with the following 4 fields per notam:
"key": The notam identity key.
"TagName": Choose the most logical Tag for this NOTAM from the list of Tags.
"TagCode": The code for the selected Tag Name.
"Explanation": In very simple English only, explain the NOTAM in a maximum of seven words, use sentence case but do not use abbreviations.
EOL,
            ],
        ];
    }
}
