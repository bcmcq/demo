<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\SocialMediaCategory;
use App\Models\SocialMediaContent;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class SocialMediaContentSeeder extends Seeder
{
    protected array $seeds = [
        [
            'category' => 'holidays',
            'title' => 'Toy Story Of Terror’ Is A Must Watch For Families This Halloween',
            'content' => 'Woody, Buzz, Jessie, Rex, Mr. Potato Head, Trixie, and Mr. Pricklepants are all stars of the show, and they go on a major road trip with their kid Bonnie. When they take a detour and end up at the rickety Sleep Well Motel, the toys find that there really are some hidden mysteries that slink among the shadows!↵↵The short film is smartly structured and intelligently executed: it references classic horror films and Halloween flicks in both plot and visual imagery — so we see the characters watching the vampire and goblin movies that they’re slowly becoming a part of as the plot thickens!↵↵New characters like Lego Bunny and a Cat PEZ dispenser are introduced, when the toys find themselves trapped by yet another overeager toy collector trying to cash in the big bucks (haven’t they realized by now that they will be outsmarted?!)  and they can easily grow to be new endearing favorites!',
        ],
        [
            'category' => 'holidays',
            'title' => 'Halloween And Blackface: Same Story, Different Year',
            'content' => "Halloween is — uh, how do you say? — high season for writing about race and culture. The list of celebrities, stores and college freshmen sporting racist costumes — plus the inevitable backlash — means these stories practically write themselves.↵↵Given the yearly onslaught of racist Halloween costumes, we sometimes joke here at Code Switch that Halloween is Blackface Christmas.* (Joking aside, here's a brief history of blackface.) It has gotten bad enough that college administrators feel compelled to fire off pre-emptive no-blackface-or-racist-costumes emails to students.↵↵This year it was the turn of Ohio University, the University of Minnesota and the University of Colorado, Boulder, which posted this note on its website: \"If you are planning to celebrate Halloween by dressing up in a costume, consider the impact your costume decision may have on others in the CU community.\"",
        ],
        [
            'category' => 'holidays',
            'title' => 'A Fat Dad Halloween (Hide the Candy)',
            'content' => 'Every year after trick-or-treating for Halloween, I used to hide my candy in a little paisley suitcase under my bed. Just to be safe, I even locked it so my dad would not devour the contents in one night.↵↵My sister April and I loved waiting till my parents were asleep and then pouring out our loot on the big orange, fuzzy rug (shaped like a foot) that adorned the middle of our bedroom. That foot was where we would decide what we were going to eat and what we were going to swap. After the negotiations were complete, we carefully secured our treats, locking the suitcase with a little gold key that I kept around my neck for safe keeping.↵↵“I just can’t resist the ones with caramel nougat or crispy wafers,” my dad would say, as he sneaked into our room begging for something, anything — just one little bag of peanut M& M’s. But days before, he had made me promise not to give in — even if he tickled me so hard that I felt as if I could not breathe. “The goblins are going to get you, the goblins are going to get you,” he would repeat in an animated voice, trying to wear me down so I would surrender my candy. “Feed me, feed me,” he begged, inhaling the candy aroma left behind on an empty wrapper he found on the bedroom floor. I knew he would be furious with both himself and me the next morning if I did not hold up my end of the bargain.',
        ],
        [
            'category' => 'holidays',
            'title' => 'Nightmare Before Christmas',
            'content' => "With Disney's animated classic A Nightmare Before Christmas back in theaters for Halloween, TTLRS's parent company Roma Music Group has unveiled a collection of lullabies based on Danny Elfman's immortal songs from the film. The entire soundtrack is represented here, and everything from the anthem \"This is Halloween,\" the softly melancholy \"Sally's Song\" and the upbeat \"What's This?\" gets the warm and fuzzy treatment.↵↵Nightmare completists will no doubt want this one in their collection, whether they have little ones or not; as with all of Roma's TTLRS releases, these songs are not novelty cash-ins, but well-written arrangements that create an even, meditative vibe while retaining the musical hooks of the source material – and you just might find yourself humming or singing along.",
        ],
        [
            'category' => 'holidays',
            'title' => 'Thanksgiving and Christmas Airfare Turbulence',
            'content' => 'The average price of tickets sold for Thanksgiving-week U.S. and Caribbean flights is up a stunning 9.4% compared with last year, according to Airlines Reporting Corp., a firm that processes tickets sold by online and traditional travel agencies. Fares on tickets sold for Christmas week have risen 7.3% over last year to an average of $337, not including taxes and fees, ARC said.↵↵The high prices pile on top of several postrecession years of sharp fare increases for holiday travel. Because airlines have consolidated and reduced the number of flights in many markets, the cost of flying has escalated at peak travel times.↵↵"Unfortunately, there is no respite in sight for holiday travelers. The trend of rising airfare is continuing," said Simon Bramley, vice president at Travelocity, a Sabre Holdings Corp. unit.',
        ],
        [
            'category' => 'holidays',
            'title' => 'Amazon to create 800 non-zero-hours jobs in runup to Christmas',
            'content' => "Online retailer Amazon is to create 800 jobs before the end of the year and make changes to shift patterns at its distribution centres, moving from five-day eight-hour shifts to four-day 10-hour shifts. The move will give workers three days off every week.↵↵Amazon's temporary workers will be offered the chance to take up the 800 new permanent posts on guaranteed hours rather than zero-hours contracts.↵↵Catherine McDermott, UK operations director at Amazon, said: \"Every one of our employees will be working over the Christmas period to delight customers by delivering millions of items on a daily basis and we are pleased to be creating 800 new permanent roles in our centres ahead of the Christmas period.",
        ],
        [
            'category' => 'holidays',
            'title' => "Early New Year's Celebrations",
            'content' => 'The earliest recorded festivities in honor of a new year’s arrival date back some 4,000 years to ancient Babylon. For the Babylonians, the first new moon following the vernal equinox—the day in late March with an equal amount of sunlight and darkness—heralded the start of a new year. They marked the occasion with a massive religious festival called Akitu (derived from the Sumerian word for barley, which was cut in the spring) that involved a different ritual on each of its 11 days.↵↵In addition to the new year, Atiku celebrated the mythical victory of the Babylonian sky god Marduk over the evil sea goddess Tiamat and served an important political purpose: It was during this time that a new king was crowned or that the current ruler’s divine mandate was symbolically renewed.',
        ],
        [
            'category' => 'holidays',
            'title' => "New Year's Eve",
            'content' => 'Doomsayers have long proclaimed the demise of classical music, although given the myriad high-quality concerts almost every night in New York, it seems the genre must be dying elsewhere. The graveyard is certainly not in China, which has seen a surge of interest in Western classical music in the last decade. The New York Philharmonic has paid tribute in recent years to this enthusiasm with a gala event celebrating the Chinese New Year.',
        ],
        [
            'category' => 'holidays',
            'title' => 'New Year Lost and Found Love as the Ball Dropped',
            'content' => 'You wouldn’t think — with the noise, the crowds and the performance-impairing quantities of alcohol — that New Year’s Eve would be the most romantic of holidays. But, at least according to the Internet, you would be wrong. The following poems about love discovered — or lost — on New Year’s Eve were “found” last week in the Missed Connections section of newyork.craigslist.org. They are printed verbatim with only line and stanza breaks added; their titles are their subject headings.',
        ],
        [
            'category' => 'seasons',
            'title' => "Shoes You'll Need to Weather the Winter Season",
            'content' => 'Ugg boots and shearling fur seem to consistently attract a crowd, but if you’re in need of some work-friendly boots, look no further. High-knee leather boots are a professional option and can be found at a a reasonable price everywhere, from Nordstrom to TJ.Maxx. Invest in a pair of these that will last years and can bring you from October to March without causing blisters or other unnecessary shoe problems. Always remember to spray protector over leather and fur boots so they don’t get harmed by the weather.↵↵Teenagers can opt for “booties” or small wedge boots that are fun and youthful options when bad weather hits. Colors like grey, black, and brown match with almost everything, which is helpful for your allowance.',
        ],
        [
            'category' => 'seasons',
            'title' => 'COPPER MOUNTAIN OPENS FOR WINTER SEASON',
            'content' => 'The American Eagle chairlift starts turning for the 2013-2014 season at 9 a.m. on Friday, Nov. 1 and at 8:30 a.m. on weekends and holidays. Throughout opening weekend, guests can experience top-to-bottom skiing and riding off the American Eagle chairlift and in the Easy Rider learning area. Woodward at Copper will feature over ten terrain park features in Woodward’s Eagle Terrain Park, catering to all ability levels.↵↵Sherpa, Copper’s new on-mountain intelligence app, is expected to be available for use and free download starting opening weekend. Don’t miss hands-free, real-time insider information, available to iPhone users for the first time at Copper; Android downloads will be available by mid-December. For the most up-to-date information on Sherpa including a demo video.',
        ],
        [
            'category' => 'seasons',
            'title' => 'Canada Winter Forecast: Abundant Snow for Ski Resorts',
            'content' => 'An active storm track through the Great Lakes during December and into a part of January will lead to above-normal precipitation this winter over a large portion of eastern Canada.↵Snowfall will end up higher than normal from the upper Great Lakes through most of Quebec and into Labrador. Farther south in Ontario, from Windsor to Toronto and Kingston, the milder pattern during December and January will lead to more mixed precipitation events, which will cut down on the snowfall potential.',
        ],
        [
            'category' => 'seasons',
            'title' => 'Summer Season at Saratoga to Include 15 Grade I Stakes',
            'content' => "A Grade III race for 2-year-old fillies at six furlongs, the Schuylerville drew eight entrants, including a pair of young ladies trained by Todd Pletcher.↵↵Cotton Blossom, a daughter of Broken Vow, will be ridden by the barn's main man, John Velazquez, as she tries for her second win. In her June 16 debut at Monmouth Park in Oceanport, N.J., Cotton Blossom beat maidens by 5 3/4 lengths as the 2-5 favorite.↵↵The other Pletcher entry is Audacious Chloe, a More Than Ready filly who won June 1 at Belmont Park after finishing second in her bow about five weeks earlier at Keeneland. Garrett Gomez will ride.↵↵Race of the day: Spot The Diplomat, an impressive maiden winner in his second career start July 8 at Hollywood Park, will try to become a stakes winner today when he faces five other California breds in the $125,000 Graduation at Del Mar. Victor Espinoza will ride the 2-year-old for trainer Carla Gaines in the 5 1/2 -furlong race that is part of a pick six with a carry-over of $140,853.",
        ],
        [
            'category' => 'seasons',
            'title' => 'The Best Family Summer Ever',
            'content' => 'Long gone are the enthusiastic days when I excitedly anticipated the end of school so I could “do nothing” for three months. ↵↵Summer was a magical time of playing with friends, staying up late, sleeping in, and taking family vacations. I look back on those memories with great fondness, but now that I’m a parent I am beginning to realize that summer can be either a really great experience for the family, or a very frustrating and exhausting one. That has led my wife and me to begin plotting out the summer for our family with more intentionality. ↵↵We know that if we don’t, summer will not only slip away very fast, but also could be full of conflict and power struggles with our kids. ↵↵One of the things that I have noticed with increasing frequency is the number of parents who want me to help them explore in family therapy what the summer is “going to look like.” Just last week I spent several sessions with different families helping them communicate realistic expectations between parent and child. And a few weeks ago, upon finishing a church parenting class on anxiety in the family, several parents came up to me to thank me for how timely the topic of anxiety was for their family as they head into the long summer.',
        ],
        [
            'category' => 'seasons',
            'title' => 'When Summer Was Easy',
            'content' => "Of all the seasons, summer seems to evoke the most childhood memories, probably because of its singular status as the Season of No School! Ah, the freedom to frolic beyond the reach of scolding teachers and paddle-wielding assistant principals, to pursue the idle arts of cloud-gazing, star-counting, making firefly lanterns and perhaps even skinny-dipping with an adventurous cheerleader (or, more likely, her less-glamorous cousin).↵↵As we grow older (I was born in 1955, during Eisenhower's first term), we are naturally inclined to compare summers then with summers now. To no one's surprise, many of us, from the heights of our knowledge, wisdom and middle-age distemper, find that summers aren't what they used to be.↵↵Let us count a few of the ways.↵↵First off, most of us born in the '50s spent summers in the raw, sweltering bosom of Mother Nature, which, with all due respect, wasn't exactly paradise, especially when the mosquitoes started feeding. Home air-conditioners were rare; only about 10% of homes had them by 1965 (around 80% of modern homes are climate-controlled). When it got hot, you turned on a fan. When it got real hot, you prayed for a thunderstorm.",
        ],
        [
            'category' => 'seasons',
            'title' => 'Spring rockfish season expected to open',
            'content' => 'Rockfish season is expected to open tomorrow, although regulations setting season dates, size and creel limits had not been approved by the state as of last evening.↵↵The proposed spring striped bass (rockfish) season for recreational and charter-boat fishermen would run from tomorrowthrough May 31 with a 32-inch minimum size and a limit of one per day and five during the spring season.↵↵Starting June 1, the minimum size drops to 28 inches and on June 17 to 26 inches. The creel limit would be one per day.↵↵According to DNR, if no decision is reached beforehand, the spring rockfish season could revert to the parameters of last year, opening on April 28 with a 32-minimum, a daily limit of one and a season limit of five.',
        ],
        [
            'category' => 'seasons',
            'title' => 'Become a Bird Enthusiast',
            'content' => "The tweets from the trees can bring you countless days of bird watching with your kids. Feed the birds, watch them through homemade binoculars and raise your children's awareness of the creatures around them. Bird watching teaches kids patience, listening and observation skills. With over 10,000 bird species to see, you never know which type of new friend you'll find hiding in the branches.",
        ],
        [
            'category' => 'seasons',
            'title' => 'Go Treasure Hunting',
            'content' => 'Letterboxing and geocaching are activities that appeal to children who crave adventure. Both sports have different goals but your whole family can play this global version of hide and seek combined with treasure hunting. Younger children will enjoy getting out of the house to find and leave treasures. Older children will love solving puzzles and clues while logging the journey in their personal notebook.',
        ],
        [
            'category' => 'trivia',
            'title' => 'Jim Cummings',
            'content' => 'Jim Cummings is the voice of Winnie the Pooh. He calls sick kids in hospitals and chats with them in character.',
        ],
        [
            'category' => 'trivia',
            'title' => 'Slumber Party Barbie',
            'content' => 'In the mid-1960s, Slumber Party Barbie came with a book called "How to Lose Weight." One of the tips was "Don’t eat."',
        ],
        [
            'category' => 'trivia',
            'title' => 'The Constitution',
            'content' => 'The Constitution does not require the Speaker of the House to be a member of the House. In 2013, Colin Powell got a vote.',
        ],
        [
            'category' => 'trivia',
            'title' => 'The first webcam',
            'content' => 'The first webcam watched a coffee pot. It allowed researchers at Cambridge to monitor the coffee situation without leaving their desks.',
        ],
        [
            'category' => 'trivia',
            'title' => 'Einstein said',
            'content' => "When asked if he knew the speed of sound, Einstein said he \"didn't carry such information in my mind since it's readily available in books.\"",
        ],
        [
            'category' => 'trivia',
            'title' => "Marie Curie's notebooks",
            'content' => "Marie Curie's notebooks are still radioactive.",
        ],
        [
            'category' => 'trivia',
            'title' => 'Hunger Games',
            'content' => 'Hunger Games author Suzanne Collins also wrote for Clarissa Explains It All.',
        ],
        [
            'category' => 'trivia',
            'title' => 'Republican',
            'content' => 'The last time a Republican was elected president without a Nixon or Bush on the ticket was 1928.',
        ],
        [
            'category' => 'trivia',
            'title' => 'LAX',
            'content' => 'When three-letter airport codes became standard, airports that had been using two letters simply added an X.',
        ],
        [
            'category' => 'facts',
            'title' => 'Sporange',
            'content' => 'There is a word that rhymes with orange! Sporange is a botany term that means "spore case."',
        ],
        [
            'category' => 'facts',
            'title' => 'Space Jam',
            'content' => 'The original Space Jam website still exists.',
        ],
        [
            'category' => 'facts',
            'title' => 'Japan offered new British PM Margaret',
            'content' => 'In 1979, Japan offered new British PM Margaret Thatcher 20 "karate ladies" for protection at an economic summit. She declined.',
        ],
        [
            'category' => 'facts',
            'title' => 'G-Mail',
            'content' => "Before Google launched Gmail, \"G-Mail\" was the name of a free email service offered by Garfield's website.",
        ],
        [
            'category' => 'facts',
            'title' => 'Cold War',
            'content' => 'During the Cold War, the U.S. considered airdropping enormous condoms labeled "Medium" on the Soviets.',
        ],
        [
            'category' => 'facts',
            'title' => 'Nikola Tesla on Thomas Edison',
            'content' => 'Nikola Tesla on Thomas Edison: "He had no hobby, cared for no amusement of any kind and lived in utter disregard of the most elementary rules of hygiene."',
        ],
        [
            'category' => 'facts',
            'title' => 'Kentucky tweaked its Wildcat logo',
            'content' => 'Kentucky tweaked its Wildcat logo in 1994 after people complained the tongue was too phallic.',
        ],
        [
            'category' => 'facts',
            'title' => 'To Kill a Mockingbird',
            'content' => 'The final speech by Gregory Peck in To Kill a Mockingbird was done in one take.',
        ],
        [
            'category' => 'facts',
            'title' => 'Detroit presented Saddam',
            'content' => 'In 1980, Detroit presented Saddam Hussein with a key to the city.',
        ],
        [
            'category' => 'jokes',
            'title' => 'Die Hard: With A Vengeance',
            'content' => 'In Die Hard: With a Vengeance (better known as Die Hard 3), Bruce Willis casually mentions to Samuel L. Jackson that when the force suspended him, he spent his time “smokin’ cigarettes and watchin’ Captain Kangaroo,” a seemingly irrelevant and throwaway line given the fact that they’re trying to catch an evil terrorist (is there any other kind?).↵↵This is actually a reference to Pulp Fiction, the seminal ’90s masterpiece that was released only a year before and also starred Bruce Willis and Samuel L. Jackson, though Jackson had the larger role in that movie.↵↵In Pulp Fiction, after Willis dispatches John Travolta’s hit man in his apartment, he is seen driving home listening to his car radio. He then celebrates his kill by singing along to The Statler Brothers’ “Flowers on the Wall,” which contains the lyrics, “Smokin’ cigarettes and watchin’ Captain Kangaroo.” The song is even featured on the movie’s soundtrack, also a ’90s classic.',
        ],
        [
            'category' => 'jokes',
            'title' => 'Waking Life',
            'content' => 'Speaking of smiley faces, indie director Richard Linklater placed a brief flash of the smiley faced logo from his accidental hit “Dazed and Confused” in the scoreboard of the pinball game Fireball in his trippy, digitally animated Waking Life, a criminally underrated gem that explores the world of sleep and dreams.↵↵Linklater was actually annoyed that the marketing team behind Dazed and Confused featured the yellow happy face on the film’s poster since he said the smiley face craze had died by 1976, the year his movie takes place. The Fireball pinball machine itself appears in Dazed and Confused, as well as in Before Sunrise, the first installment of his critically acclaimed Before series starring Ethan Hawke and Julie Delpy.',
        ],
        [
            'category' => 'jokes',
            'title' => 'Teenage Mutant Ninja Turtles',
            'content' => 'If you were a kid growing up in the late ’80s/early ’90s as I was, you couldn’t escape the pop culture juggernaut that was Teenage Mutant Ninja Turtles (or TMNT for short). It was a comic book, a cartoon series, a video game, a line of collectible toys, and finally, its inevitable incarnation as a live-action feature film.↵↵The movie’s epilogue paid tribute to its humble graphic novel origins though when, after being interrogated by a ball-busting Chief Sterns, a random, nameless hood — played by a then unknown Sam Rockwell—explains, “Check out East Warehouse on Lairdman Island.”↵↵True TMNT fans know that the titular creatures were created by Kevin Eastman and Peter Laird, so this little line of dialogue actually swapped parts of their last names. Also, earlier in the movie, boxes in this warehouse near the skate half-pipe read “Mirage.” Mirage Comics is the company that originally published the TMNT comics.',
        ],
        [
            'category' => 'jokes',
            'title' => 'Funny People',
            'content' => "I use so much hand lotion when I am masturbating that when I see people in public using hand lotion I'm thinking, \"Holy shit, they're about to jerk off!\"",
        ],
        [
            'category' => 'jokes',
            'title' => 'Punchline',
            'content' => 'Hello, my name is Lilah Krytsick, and on our wedding night, my husband gave me something very long and hard... a new name.',
        ],
        [
            'category' => 'jokes',
            'title' => "Shoot 'Em Up",
            'content' => 'There once was a woman who was quite begat. She had three babies named Nat, Pat, and Tat. ↵↵She said it was fun in the breeding, but found it was hell in the feeding when she saw there was no tit for Tat.',
        ],
        [
            'category' => 'jokes',
            'title' => 'What about Bob?',
            'content' => "“The doctor draws two circles and says ‘What do you see?’ the guy says ‘Sex.’” ↵↵So the doctor draws trees, ‘What do you see?’ The guy says ‘sex.’ The doctor draws a car, owl, ‘Sex, sex, sex.’ ↵↵The doctor says to him. ‘You are obsessed with sex!’ He replies, ‘Well you're the one drawing all the dirty pictures!’”",
        ],
        [
            'category' => 'jokes',
            'title' => "White Men Can't Jump",
            'content' => 'I seen your mother kicking a can down the street. I said ‘What you doin’?’ and she said ‘Moving’',
        ],
        [
            'category' => 'jokes',
            'title' => 'Pulp Fiction',
            'content' => 'Three tomatoes are walking down the street - a poppa tomato, a momma tomato, and a little baby tomato. ↵↵Baby tomato starts lagging behind. Poppa tomato gets angry, goes over to the baby tomato, and smooshes him... and says, "Catch up."',
        ],
        [
            'category' => 'news',
            'title' => 'Stranded camper saved from starvation',
            'content' => "If Marco Lavoie had not been rescued when he was, the Quebec man probably would have perished within 48 hours.↵↵He was hypothermic, dehydrated and near starvation in the unforgiving cold of northwestern Quebec when police rescued him Wednesday.↵↵Lavoie's survival might be called miraculous, as he had been in the wilderness for more than three months.↵↵The 44-year-old is an experienced outdoorsman, but an encounter with a bear changed his luck, Quebec police said↵↵At some point during a planned two-month canoe trek in the Lake Matagami area, a bear attacked Lavoie's campsite, eating his food and ruining his equipment, police spokeswoman Christine Coulombe said.↵↵The bear did not injure Lavoie but left him without the tools or resources to survive. Police say they have yet to speak with Lavoie to get details.",
        ],
        [
            'category' => 'news',
            'title' => 'Kraft removing artificial dyes from some mac and cheese',
            'content' => "When SpongeBob SquarePants skips onto shelves in boxes of Kraft Macaroni & Cheese next year, he may be a little less, well, yellow than your kids are used to.↵↵Kraft has revamped its character-shaped product line for 2014, according to company spokeswoman Lynne Galia. The new versions will have six additional grams of whole grains, be lower in sodium and saturated fat, and will use spices instead of artificial food dyes to recreate the pasta's famous yellow-orange color.↵↵\"Parents have told us that they would like fun Mac & Cheese varieties with the same great taste, but with improved nutrition,\" Galia said in an e-mail.↵↵The company will remove Yellow No. 5 and Yellow No. 6 from boxes containing pasta shaped like SpongeBob SquarePants and those with Halloween and winter shapes. Two new shapes of the popular pasta -- Nickelodeon's Teenage Mutant Ninja Turtles and \"How to Train Your Dragon 2\" from Dreamworks -- will also be free of food coloring, Galia said.",
        ],
        [
            'category' => 'news',
            'title' => 'Stocks end week on a high note',
            'content' => "For the week, the Dow and the S&P 500 ended up a bit, while the Nasdaq posted a small loss.↵↵Stocks are coming off a strong month. The S&P 500 gained 5% in October, a significant jump considering the government was shut down for more than two weeks. The Dow and S&P 500 are both hovering near record highs.↵↵If history is any guide, the good times should keep on rolling. The S&P 500 has gained in November more than 60% of the time over the past 30 years, according to Schaeffer's Investment Research.↵↵Ryan Detrick, senior technical analyst Schaeffer's, said November and December have been even stronger in years when stocks have rallied during the first quarter. The theory is that investors who missed the early gains try to make up for it in the final months of the year, he said.↵↵\"We expect to see aggressive buyers into the end of year on any and all pullbacks,\" said Detrick.",
        ],
        [
            'category' => 'news',
            'title' => ' Mystery barge will be Google Glass showroom',
            'content' => "The mystery barge in San Francisco Bay is indeed a Google project, and houses a fancy, floating showroom for Google Glass and other products, a CNN affiliate is reporting.↵↵According to KPIX-TV in San Francisco, the barge structure will become one of a small fleet of luxury event spaces, complete with a party deck for invitation-only visitors.↵↵The station cited \"multiple sources\" including at least one who has been aboard the barge, and said it was dreamed up at Google X, the company's secret facility where engineers have cooked up projects like Google Glass and a self-driving car.↵↵The facility is constructed from interchangeable shipping containers and is designed to be disassembled and transported via barge or train to other locations, a source said.",
        ],
        [
            'category' => 'news',
            'title' => 'Google releases Nexus 5 phone with Kit Kat',
            'content' => "Google on Thursday released its latest candy-themed mobile operating system: Android 4.4, more deliciously known as Kit Kat.↵↵The system will launch immediately on Google's new Nexus 5 phone and then roll out to other Nexus devices, the Samsung Galaxy S4 and HTC One, in the next few weeks.↵↵Nexus devices are Google's flagship line of phones and tablets, running the newest and purest form of Android and available without contracts. The Nexus 5, manufactured by LG, is built to work internationally on a variety of bands and with whatever local SIM card you pick up, though Verizon's band is still not supported.↵↵Physically, the 5-inch Nexus is a bit thinner and lighter than the previous generation, while packing a faster Snapdragon 800 processor. It has optical image stabilization and a new HDR camera feature for combining multiple exposures into a single photo, lightening up dark areas and bringing details back to images blown out by a flash.",
        ],
        [
            'category' => 'news',
            'title' => 'The CNN 10: Thinkers',
            'content' => "The phones in our pockets and the Web at our fingertips. The way we live, the ways our children learn and the discoveries that make us excited about the days ahead.↵↵None materialized out of thin air.↵↵Great advances come from great ideas. And great ideas come from great thinkers.↵↵CNN is honoring the visionaries whose ideas are shaping our future by highlighting 10 of our favorite thinkers in science and technology. These are people who have shoved conventional wisdom aside and are changing the world with their insights and innovations.↵↵We consulted our colleagues and examined a range of fields to assemble our list. They come from different countries, backgrounds and disciplines, but all our honorees are actively working to make an impact for the greater good. Their ideas run a wide gamut, from making the world more energy efficient to using data to fight disease to finding new ways to continue humanity's quest into outer space.↵↵They inspire us, and we hope they will inspire you as well.",
        ],
        [
            'category' => 'news',
            'title' => 'Samsung edges Apple in tablet-satisfaction survey',
            'content' => "Samsung tablets have edged out iPads for the first time in JD Power's biannual customer-satisfaction survey, although some observers say the results don't quite add up.↵↵Samsung topped JD Power's rankings with a score of 835 out of 1,000, narrowly beating out Apple's 833. Amazon was third in customer satisfaction at 826, followed by Asus and Acer.↵↵The rankings are based on the experiences of 3,375 tablet owners who responded between March and August of this year, and don't include the new iPad Air and iPad Mini, which went on sale Friday.↵↵But some tech pundits questioned the market-research company's scoring system.↵↵Survey respondents scored Apple's iPad higher than Samsung's tablets in four of five categories -- performance, ease of operation, styling/design, and features -- while Samsung scored higher in just one: cost.",
        ],
        [
            'category' => 'news',
            'title' => 'Google engineers tapped to revive HealthCare.gov',
            'content' => "Experts from Google and other prominent tech companies are joining a so-called tech surge to fix the struggling HealthCare.gov website.↵↵The agency overseeing the Obamacare site announced the additions Thursday, after the site had crashed for over 36 hours.↵↵The Centers for Medicare and Medicaid Services said the surge includes \"dozens\" of people with expertise in the site's key issues: reliability, stability, and large-scale operations.↵↵Besides government employees, the surge includes employees from Google (GOOG, Fortune 500), as well as employees of software giants Red Hat (RHT) and Oracle (ORCL, Fortune 500).",
        ],
        [
            'category' => 'news',
            'title' => "Don't freak out about Facebook's 'teen problem'",
            'content' => "Investors punished Facebook's shares Wednesday after the company admitted young teens aren't using the site as much, but Wall Street analysts think the reaction is way overblown.'↵↵\"We did see a decrease in [teenage] daily users [during the quarter], especially younger teens,\" Facebook chief financial officer David Ebersman said during the company's third-quarter earnings call on Wednesday. The comments were surprising given that just last quarter, CEO Mark Zuckerberg said reports of declining teen usage were \"just not true.\"",
        ],
        [
            'category' => 'funny_videos',
            'title' => 'Funny Babies with Funny Dog German Shepherd',
            'content' => 'This is a funny little dog',
        ],
        [
            'category' => 'funny_videos',
            'title' => 'Denver Guilty Dog',
            'content' => 'The funny part is that Macy actually did it.﻿',
        ],
        [
            'category' => 'funny_videos',
            'title' => 'Cutest Puppies Ever',
            'content' => 'Cuteness Overload',
        ],
        [
            'category' => 'funny_videos',
            'title' => 'Husky Einstein puppy',
            'content' => 'Husky Einstein puppy discovers a tennis ball',
        ],
        [
            'category' => 'funny_videos',
            'title' => 'Siberian VS pool robot',
            'content' => 'A Siberian husky who want to fight with a pool robot movin like a snake ^^',
        ],
        [
            'category' => 'funny_videos',
            'title' => 'Captain Swimming',
            'content' => 'Captain the dog swimming the length of the pool (~35 feet) in one breath!',
        ],
        [
            'category' => 'funny_videos',
            'title' => 'Baby Beagle (Maxwell) Playing with Older Beagle (Heidi)',
            'content' => 'We just brought home our new six week old beagle Max. We were a little worried about how our 10 year old beagle Heidi would react, but they seem to be having fun playing.',
        ],
        [
            'category' => 'funny_videos',
            'title' => 'Charlie, our Beagle, talking',
            'content' => 'Charlie learning his inside voice',
        ],
        [
            'category' => 'funny_videos',
            'title' => 'The cutest talking beagle you will ever see!!',
            'content' => 'This is my beagle Chet, he is so cute and he even talks a little.',
        ],
        [
            'category' => 'current_events',
            'title' => 'LAX shooting: Latest on suspect, victims and warning that may have come too late',
            'content' => "After a weekend of intense investigation, authorities are piecing together more details about Friday's fatal shooting at Los Angeles International Airport, including the suspect's behavior earlier in the week and a warning from his family that may have come minutes too late.↵↵Officers sent to check on Paul Ciancia's welfare arrived at his apartment less than an hour after the shooting started, police said Monday.↵Here is a rundown to get you up to speed:↵↵THE SUSPECT↵↵Ciancia, 23, of Los Angeles is charged with murder of a federal officer and commission of violence in an international airport.↵↵He was shot by officers Friday and was in critical condition at Ronald Reagan UCLA Medical Center on Sunday.",
        ],
        [
            'category' => 'current_events',
            'title' => 'Obamacare website goes dark for nightly fix-it work',
            'content' => "Insomniacs and chronic wee-hour shoppers: Take shopping for Obamacare insurance on the federally run HealthCare.gov website off your overnight shopping lists -- at least for a while.↵↵The part of the problem-plagued website that allows people to apply for coverage will now be taken offline nightly between 1 and 5 a.m. ET, according to a banner now appearing atop the site's home page.↵↵\"The Health Insurance Marketplace online application isn't available from approximately 1 a.m. to 5 a.m. EST daily while we make improvements,\" the banner reads. \"Additional down times may be possible as we work to make things better. The rest of the site and the Marketplace call center remain available during these hours.\"",
        ],
        [
            'category' => 'current_events',
            'title' => 'Awe-inspiring animal migrations that send people flocking',
            'content' => "There is something magical about animal migrations. Perhaps it's the fact that as more of us move to cities (The U.N. predicts that nearly 70% of the global population will be urban dwellers by 2050), the sight of congregating herds, schools of fish, or flocks of birds is increasingly rare. Or maybe witnessing animals cross great distances to eat, mate, breed and, in essence, survive helps us to take stock of our own lives.↵↵Whatever the reason, when animals come together, very often so do humans. Here's our guide to the animal migrations that bring people out in flocks.",
        ],
        [
            'category' => 'current_events',
            'title' => "NASA's next Mars probe ready for Nov. launch, despite gov't slimdown",
            'content' => "NASA's next Mars probe should get off the ground on time, no matter how long the government shutdown lasts.↵↵The Mars Atmosphere and Volatile Evolution orbiter, or Maven, got back on track for a Nov. 18 launch on Thursday (Oct. 3), just two days after the government shutdown froze liftoff preparations and put a scare into planetary scientists around the world.↵↵\"We have already restarted spacecraft processing at Kennedy Space Center, working toward being ready to launch on Nov. 18,\" Maven principal investigator Bruce Jakosky, of the University of Colorado, Boulder, wrote in a mission status update Thursday. \"We will continue to work over the next couple of days to identify any changes in our schedule or plans that are necessary to stay on track.\"",
        ],
        [
            'category' => 'current_events',
            'title' => 'Farmer stumbles across 2,000-year-old sarcophagus while planting coffee in Bali',
            'content' => 'A man in Bali stumbled across a two-millennia-old sarcophagus on Thursday while plowing land to plant coffee seeds, the Jakarta Globe reported.↵↵"The shape is like a stone coffin," said Denpasar Archaeology Agency head Wayan Suantika when describing the ancient find. "More or less two meters long and one meter in width."↵↵A preliminary examination of the sarcophagus indicated that the artifact dates back to approximately 2,300 years.',
        ],
        [
            'category' => 'current_events',
            'title' => "Nazi looted art 'found in Munich' - German media",
            'content' => 'A collection of 1,500 artworks confiscated by the Nazis in the 1930s and 1940s has been found in the German city of Munich, media reports say.↵↵The trove is believed to include works by Matisse, Picasso and Chagall, the news magazine Focus reports.↵↵Some of the works were declared as degenerate by the Nazis, while others were stolen from or forcibly sold for a pittance by Jewish art collectors.↵↵If confirmed, it would be one of the largest recoveries of looted art.↵↵Investigators put the value of the works at about one billion euros (£846m; $1.35bn), Focus said.',
        ],
        [
            'category' => 'current_events',
            'title' => "'Anonymous Indonesia' attacks Australian websites",
            'content' => "A hacking group calling itself Anonymous Indonesia claims to have attacked over 170 Australian sites, following reports of Australian spying.↵↵The sites affected seemed to belong primarily to small businesses.↵↵On Friday, Indonesia summoned Australia's ambassador amid reports that its Jakarta embassy was used as part of a US-led spying network.↵↵The reports were based on a US National Security Agency document leaked by whistleblower Edward Snowden.↵↵The allegations caused anger in Indonesia, which is a key ally and trading partner of Australia.↵↵The Australian government has declined to comment on the reports, saying it does not comment on intelligence matters.↵↵Indonesian Foreign Minister Marty Natalegawa told reporters on Monday: \"Enough is enough\".↵↵\"While [the US and Australia] are not able to confirm or deny past activities, at least they should be able, and I'm making a public expectation here, I think they should be able to say henceforth they are not going to do it anymore,\" he said.",
        ],
        [
            'category' => 'current_events',
            'title' => 'Johnson & Johnson fined $2.2bn to settle drug cases',
            'content' => 'Drugs giant Johnson & Johnson has agreed to pay more than $2.2bn (£1.4bn) to settle allegations over its marketing techniques, the Justice Department has said.↵↵The firm is alleged to have paid incentives to doctors and pharmacies to promote three of its medicines.↵↵These included anti-psychotic drugs Risperdal and Invega, as well as heart drug Natrecor.↵↵The firm also allegedly promoted the drugs for uses not officially allowed.↵↵While US doctors can prescribe medicines as they see fit, pharmaceutical companies cannot advocate any use of their drugs that has not been approved by the Food and Drug Administration (FDA).',
        ],
        [
            'category' => 'current_events',
            'title' => 'Rare solar eclipse in America, Europe and Africa',
            'content' => 'A rare solar eclipse allowing a view of the Sun that is totally or partially blocked by the Moon has taken place.↵↵It was first visible in the southern United States, before sweeping east across the Atlantic Ocean and the African continent.↵↵The US space agency, Nasa, said the greatest total eclipse occurred over the Atlantic Ocean.↵↵One of the best views was in northern Kenya, where tour companies organised trips to view a total blackout.↵↵Local myths there attribute the event to the Moon eating the Sun.',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        Account::all()
            ->each(function ($account) {
                // Give account 1 some extra
                $contentCount = $account->id === 1 ? 50 : 10;
                $contents = $this->generateContentForAccount($account, $contentCount);

                $this->scheduleContentForAccount($account, $contents);
            });
    }

    /**
     * @return Collection<SocialMediaContent>
     */
    protected function generateContentForAccount(Account $account, int $count): Collection
    {
        $categories = SocialMediaCategory::all();

        return collect($this->seeds)
            ->random($count)
            ->map(fn ($data) => SocialMediaContent::create([
                'account_id' => $account->id,
                'title' => $data['title'],
                'content' => $data['content'],

                'social_media_category_id' => $categories->where('name', $data['category'])->first()->id,
            ]));
    }

    /**
     * @param  Collection<SocialMediaContent>  $availableContent
     */
    protected function scheduleContentForAccount(Account $account, Collection $availableContent)
    {
        $faker = Factory::create();

        $availableContent
            ->random(rand(0, $availableContent->count()))
            ->each(function (SocialMediaContent $content) use ($account, $faker) {
                $account->scheduledSocialmediaPosts()->create([
                    'social_media_content_id' => $content->id,
                    'scheduled_at' => $faker->dateTimeBetween('now', '+2 months'),
                ]);
            });

        $availableContent
            ->random(rand(0, $availableContent->count()))
            ->each(function (SocialMediaContent $content) use ($account, $faker) {
                $account->postedSocialmediaPosts()->create([
                    'social_media_content_id' => $content->id,
                    'posted_at' => $faker->dateTimeBetween('-2 months'),
                ]);
            });
    }
}
