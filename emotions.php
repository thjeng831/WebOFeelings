<?php
$joywords = array('joy', 'ecstasy', 'serenity', 'happy', 'playful', 'content', 'interested', 
                'proud', 'accepted', 'powerful', 'peaceful', 'optimistic', 'successful', 'respected', 'loving', 'thankful');
$trustwords = array('trust', 'acceptance', 'admiration', 'assurance', 'assuredness', 'certainty', 'certitude', 'confidence', 'conviction', 
                'credence', 'expectation', 'faith', 'hope', 'integrity', 'genuine', 'positive');
$fearwords = array('fear', 'terror', 'apprehension', 'scared', 'anxious', 'insecure', 'weak', 'rejected', 'threatened',
                'helpless', 'frightened', 'worried', 'worthless', 'inferior', 'excluded', 'nervous');
$surprisewords = array('surprise', 'amazement', 'distraction', 'startled', 'confused', 'amazed', 'excited', 'shocked', 'dismayed',
                'disillusioned', 'perplexed', 'astonished', 'awe', 'eager', 'energetic', 'wonder');
$sadnesswords = array('sad', 'grief', 'pensiveness', 'lonely', 'vulnerable', 'despair', 'guilty', 'depressed', 
                'hurt', 'isolated', 'abandoned', 'fragile', 'grief', 'ashamed', 'inferior', 'disappointed');
$disgustwords = array('disgust', 'loathing', 'boredom', 'disapproving', 'distaste', 'awful', 'repelled', 'judgemental', 'embarassed',
                'appalled', 'revolted', 'nauseated', 'detestable', 'horrified', 'hesitant', 'hate');
$angerwords = array('anger', 'rage', 'annoyance', 'humiliated', 'bitter', 'mad', 'aggressive', 'frustrated', 'critical',
                'resentful', 'ridiculed', 'violated', 'furious', 'jealous', 'infuriated', 'annoyed');
$anticipationwords = array('anticipation', 'vigilance', 'interest', 'contemplation', 'expectance', 'prospect', 'foresight', 'impatient', 'alertness',
                'caution', 'diligence', 'attention', 'concern', 'suspense', 'affection', 'enthusiasm');
$forwardwords = array('joy', 'trust', 'anticipation', 'love', 'acceptance', 'awe', 'aggressiveness', 'inspiration', 
                'optimism', 'ecstasy', 'admiration', 'amazement', 'rage', 'vigilance', 'interest');
$backwardwords = array('fear', 'surprise', 'sadness', 'disgust', 'anger', 'submission', 'apprehension', 'disapproval', 
                'remorse', 'contempt', 'annoyance', 'terror', 'grief', 'loathing');
$neutralwords = array('serenity', 'distraction', 'pensiveness', 'boredom');

//reading in more emotion words
$filejoy = file('joy.txt');
foreach($filejoy as $line_num => $line) {
    $parts = preg_split('/\s+/', $line);
    $joywords[] = $parts[0];
}
$filetrust = file('trust.txt');
foreach($filetrust as $line_num => $line) {
    $parts = preg_split('/\s+/', $line);
    $trustwords[] = $parts[0];
}
$filefear = file('fear.txt');
foreach($filefear as $line_num => $line) {
    $parts = preg_split('/\s+/', $line);
    $fearwords[] = $parts[0];
}
$filesurprise = file('surprise.txt');
foreach($filesurprise as $line_num => $line) {
    $parts = preg_split('/\s+/', $line);
    $surprisewords[] = $parts[0];
}
$filesadness = file('sadness.txt');
foreach($filesadness as $line_num => $line) {
    $parts = preg_split('/\s+/', $line);
    $sadnesswords[] = $parts[0];
}
$filedisgust = file('disgust.txt');
foreach($filedisgust as $line_num => $line) {
    $parts = preg_split('/\s+/', $line);
    $disgustwords[] = $parts[0];
}
$fileanger = file('anger.txt');
foreach($fileanger as $line_num => $line) {
    $parts = preg_split('/\s+/', $line);
    $angerwords[] = $parts[0];
}
$fileanticipation = file('anticipation.txt');
foreach($fileanticipation as $line_num => $line) {
    $parts = preg_split('/\s+/', $line);
    $anticipationwords[] = $parts[0];
}

?>