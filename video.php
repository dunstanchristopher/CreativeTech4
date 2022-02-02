<?php

require "vendor/autoload.php";

use JSON2Video\Scene;
use JSON2Video\Movie;

$json2video_apikey = "--- YOUR JSON2VIDEO API KEY ----";
$newsapi_apikey = "---- YOUR NEWSAPI.ORG API KEY ----";

// Get the news from the API
$api_url = "https://newsapi.org/v2/top-headlines?category=science&country=us&apiKey=" . $newsapi_apikey;
//$api_response = file_get_contents($api_url);
$api_response = file_get_contents("news2.json");
$api_json = json_decode($api_response, true);
if (!$api_json) die("Error reading NewsAPI");

// Parse the response and create a final JSON
$articles = [];
$num_articles = 0;

foreach ($api_json["articles"] as $article) {

    // Bypass any URL that includes ".gif"
    if (strpos($article["urlToImage"], ".gif")!==false) continue;

    // Remove anything from " - " and after
    $source_position = strpos($article["title"], " - ");
    if ($source_position!==false) $headline = substr($article["title"], 0, $source_position);
    else $headline = $article["title"];

    // Convert to human readible date
    $human_date = date("M jS", strtotime($article["publishedAt"]));

    // Add a new article to the list
    $articles[] = [
        "headline" => $headline,
        "date" => $human_date,
        "image" => $article["urlToImage"],
        "source" => $article["source"]["name"]
    ];

    // Limit the number of articles in the list
    $num_articles++;
    if ($num_articles>3) break;
}


// Create a new movie
$movie = new Movie;

// Set your API key
// Get your free API key at https://json2video.com
$movie->setAPIKey($json2video_apikey);

// Set a project ID
$movie->project = "tutorial";

// Set movie quality: low, medium, high
$movie->quality = "high";

// Set movie dimensions for an Instagram story
$movie_width = 607;
$movie_height = 1080;
$margin = 50;

$movie->width = $movie_width;
$movie->height = $movie_height;


// Intro bumper
$intro = new Scene;

$intro->addElement([
    "type" => "text",
    "template" => "basic/022",
    "settings" => [
        "slide" => "right"
    ],
    "items" => [
        [
            "text" => "SCIENCE",
            "font-size" => 50,
            "background-color" => "#FF0000",
            "color" => "#FFFFFF"
        ],
        [
            "text" => "TODAY",
            "font-size" => 50,
            "background-color" => "#FFFFFF",
            "color" => "#000000"
        ]
    ],
    "x" => $margin,
    "y" => $movie_height/2
]);

$movie->addScene($intro);


// Create an scene for each article

foreach ($articles as $key => $article) {
    
    $scene =  new Scene;

    // Add the article's image as background with pan effect
    $scene->addElement([
        "type" => "image",
        "src" => $article["image"],
        "scale" => [
            "height" => $movie_height
        ],
        "x" => intval(-($movie_height*16/9)/2 + $movie_width/2),
        "zoom" => 5,
        "pan" => ($key % 2) ? "right" : "left"
    ]);

    // Add the headline at the bottom using the basic/021 template
    $scene->addElement([
        "type" => "text",
        "template" => "basic/021",
        "settings" => [
            "left-bar-color" => "#FFFFFF",
            "vertical-align" => "bottom"
        ],
        "items" => [
            [
                "text" => $article["headline"],
                "background-color" => "#000000",
                "color" => "#FFFFFF",
                "font-size" => 40
            ]
        ],
        "width" => intval($movie_width - $margin*2),
        "x" => intval($margin),
        "y" => intval(-$margin)
    ]);

    // Add the publisher name and date at the top-left
    // keeping a margin
    $scene->addElement([
        "type" => "text",
        "template" => "basic/022",
        "settings" => [
            "slide" => "right"
        ],
        "items" => [
            [
                "text" => $article["source"],
                "font-size" => 20,
                "background-color" => "#FF0000",
                "color" => "#FFFFFF"
            ],
            [
                "text" => $article["date"],
                "font-size" => 20,
                "background-color" => "#FFFFFF",
                "color" => "#000000"
            ]
        ],
        "x" => $margin,
        "y" => $margin
    ]);

    // Generate the voice over from the headline
    // using the male voice en-US-GuyNeural
    $scene->addElement([
        "type" => "voice",
        "voice" => "en-US-GuyNeural",
        "text" => $article["headline"],
        "start" => 0.5,
        "extra-time" => 0.5
    ]);

    // Add the scene into the movie
    $movie->addScene($scene);
}

// Add a closing scene
$closing = new Scene;
$closing->addElement([
    "type" => "text",
    "template" => "basic/008",
    "items" => [
        [
            "text" => "Powered by JSON2Video.com",
            "font-size" => 20
        ]
    ],
    "x" => $margin,
    "y" => $movie_height/2
]);
$movie->addScene($closing);


// Add a background music to the movie
$movie->addElement([
    "type" => "audio",
    "src" => "https://assets.json2video.com/assets/audios/news-opening-01.mp3",
    "volume" => 0.2
]);

// Call JSON2Video API and start rendering the movie
$movie->render();

// Wait for the render to finish
$movie->waitToFinish();


