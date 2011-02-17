<?php

function Ariel_Autoload($class)
{
	// Already loaded
	if (class_exists($class, false)) {
		return;
	}

    $file = str_replace('_',DIRECTORY_SEPARATOR,$class) . '.php';

	foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
		if (file_exists($path . DIRECTORY_SEPARATOR . $file)) {
			require_once($path . DIRECTORY_SEPARATOR . $file);
			return;
		}
	}

    throw new Exception('Class ' . $class . ' not found');
}

// register autoloader
spl_autoload_register('Ariel_Autoload');

include('/home/ferret/www/ServerConfig.php');

$config = json_decode(file_get_contents('config.json'), true);

$lexer = new English_Lexer($config['db']['host'],$config['db']['user'],$config['db']['pass'],$config['db']['db']);
$string = "ONE early morning in the Springtime, when I was wandering among the hills at the back of the town, I happened to come upon a hawk with a squirrel in its claws. It was standing on a rock and the squirrel was fighting very hard for its life. The hawk was so frightened when I came upon it suddenly like this, that it dropped the poor creature and flew away. I picked the squirrel up and found that two of its legs were badly hurt. So I carried it in my arms back to the town.  When I came to the bridge I went into the musselman's hut and asked him if he could do anything for it. Joe put on his spectacles and examined it carefully. Then he shook his head.  \"Yon crittur's got a broken leg,\" he said--\"and another badly cut an' all. I can mend you your boats, Tom, but I haven't the tools nor the learning to make a broken squirrel seaworthy. This is a job for a surgeon--and for a right smart one an' all. There be only one man I know who could save yon crittur's life. And that's John Dolittle.\"  \"Who is John Dolittle?\" I asked. \"Is he a vet?\"  \"No,\" said the mussel-man. \"He's no vet. Doctor Dolittle is a nacheralist.\"  \"What's a nacheralist?\"  \"A nacheralist,\" said Joe, putting away his glasses and starting to fill his pipe, \"is a man who knows all about animals and butterflies and plants and rocks an' all. John Dolittle is a very great nacheralist. I'm surprised you never heard of him--and you daft over animals. He knows a whole lot about shellfish--that I know from my own knowledge. He's a quiet man and don't talk much; but there's folks who do say he's the greatest nacheralist in the world.\"  \"Where does he live?\" I asked.  \"Over on the Oxenthorpe Road, t'other side the town. Don't know just which house it is, but 'most anyone 'cross there could tell you, I reckon. Go and see him. He's a great man.\"  So I thanked the mussel-man, took up my squirrel again and started oft towards the Oxenthorpe Road.  The first thing I heard as I came into the marketplace was some one calling \"Meat! M-E-A-T!\"  \"There's Matthew Mugg,\" I said to myself. \"He'll know where this Doctor lives. Matthew knows everyone.\"  So I hurried across the market-place and caught him up.  \"Matthew,\" I said, \"do you know Doctor Dolittle?\"  \"Do I know John Dolittle!\" said he. \"Well, I should think I do! I know him as well as I know my own wife--better, I sometimes think. He's a great man--a very great man.\"  \"Can you show me where he lives?\" I asked. \"I want to take this squirrel to him. It has a broken leg.\"  \"Certainly,\" said the cat's-meat-man. \"I'll be going right by his house directly. Come along and I'll show you.\"  So off we went together.  \"Oh, I've known John Dolittle for years and years,\" said Matthew as we made our way out of the market-place. \"But I'm pretty sure he ain't home just now. He's away on a voyage. But he's liable to be back any day. I'll show you his house and then you'll know where to find him.\"  All the way down the Oxenthorpe Road Matthew hardly stopped talking about his great friend, Doctor John Dolittle--\"M. D.\" He talked so much that he forgot all about calling out \"Meat!\" until we both suddenly noticed that we had a whole procession of dogs following us patiently.  \"Where did the Doctor go to on this voyage?\" I asked as Matthew handed round the meat to them.  \"I couldn't tell you,\" he answered. \"Nobody never knows where he goes, nor when he's going, nor when he's coming back. He lives all alone except for his pets. He's made some great voyages and some wonderful discoveries. Last time he came back he told me he'd found a tribe of Red Indians in the Pacific Ocean--lived on two islands, they did. The husbands lived on one island and the wives lived on the other. Sensible people, some of them savages. They only met once a year, when the husbands came over to visit the wives for a great feast--Christmas-time, most likely. Yes, he's a wonderful man is the Doctor. And as for animals, well, there ain't no one knows as much about 'em as what he does.\"  \"How did he get to know so much about animals?\" I asked.  The cat's-meat-man stopped and leant down to whisper in my ear.  \"HE TALKS THEIR LANGUAGE,\" he said in a hoarse, mysterious voice.  \"The animals' language?\" I cried.  \"Why certainly,\" said Matthew. \"All animals have some kind of a language. Some sorts talk more than others; some only speak in sign-language, like deaf-and-dumb. But the Doctor, he understands them all--birds as well as animals. We keep it a secret though, him and me, because folks only laugh at you when you speak of it. Why, he can even write animal-language. He reads aloud to his pets. He's wrote history-books in monkey-talk, poetry in canary language and comic songs for magpies to sing. It's a fact. He's now busy learning the language of the shellfish. But he says it's hard work--and he has caught some terrible colds, holding his head under water so much. He's a great man.\"  \"He certainly must be,\" I said. \"I do wish he were home so I could meet him.\"  \"Well, there's his house, look,\" said the cat's, meat-man--\"that little one at the bend in the road there--the one high up--like it was sitting on the wall above the street.\"  We were now come beyond the edge of the town. And the house that Matthew pointed out was quite a small one standing by itself. There seemed to be a big garden around it; and this garden was much higher than the road, so you had to go up a flight of steps in the wall before you reached the front gate at the top. I could see that there were many fine fruit trees in the garden, for their branches hung down over the wall in places. But the wall was so high I could not see anything else. When we reached the house Matthew went up the steps to the front gate and I followed him. I thought he was going to go into the garden; but the gate was locked. A dog came running down from the house; and he took several pieces of meat which the cat's-meat-man pushed through the bars of the gate, and some paper bags full of corn and bran, I noticed that this dog did not stop to eat the meat, as any ordinary dog would have done, but he took all the things back to the house and disappeared. He had a curious wide collar round his neck which looked as though it were made of brass or something. Then we came away.  \"The Doctor isn't back yet,\" said Matthew, \"or the gate wouldn't be locked.\"  \"What were all those things in paper-bags you gave the dog?\" I asked.  \"Oh, those were provisions,\" said Matthew--\"things for the animals to eat. The Doctor's house is simply full of pets. I give the things to the dog, while the Doctor's away, and the dog gives them to the other animals.\"  \"And what was that curious collar he was wearing round his neck?\"  \"That's a solid gold dog-collar,\" said Matthew. \"It was given to him when he was with the Doctor on one of his voyages long ago. He saved a man's life.\"  \"How long has the Doctor had him?\" I asked.  \"Oh, a long time. Jip's getting pretty old now. That's why the Doctor doesn't take him on his voyages any more. He leaves him behind to take care of the house. Every Monday and Thursday I bring the food to the gate here and give it him through the bars. He never lets any one come inside the garden while the Doctor's away--not even me, though he knows me well. But you'll always be able to tell if the Doctor's back or not--because if he is, the gate will surely be open.\"  So I went off home to my father's house and put my squirrel to bed in an old wooden box full of straw. And there I nursed him myself and took care of him as best I could till the time should come when the Doctor would return. And every day I went to the little house with the big garden on the edge of the town and tried the gate to see if it were locked. Sometimes the dog, Jip, would come down to the gate to meet me. But though he always wagged his tail and seemed glad to see me, he never let me come inside the garden. ";
foreach (explode('.', $string) as $str) {
	if (strlen($str) > 0) {
		$arr = $lexer->lex($str);
		//echo '<br />';
		print_r($arr);
		echo '<br />';
	}
}