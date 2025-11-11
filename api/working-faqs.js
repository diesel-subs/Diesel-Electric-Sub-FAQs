// Simple working API with real submarine FAQ data
export default async function handler(req, res) {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const { action, category_id, q } = req.query;

  // Real submarine FAQ data
  const categories = [
    { id: 1, name: "Hull and Compartments", description: "Learn about submarine construction, hull design, and compartment layouts." },
    { id: 2, name: "US WW2 Subs in General", description: "General information about American submarines during World War II." },
    { id: 3, name: "Life Aboard WW2 US Subs", description: "Daily life, living conditions, and crew experiences aboard submarines." },
    { id: 4, name: "Operating US Subs in WW2", description: "Operational procedures, tactics, and submarine warfare techniques." },
    { id: 5, name: "Attacks and Battles, Small and Large", description: "Combat operations, battles, and military engagements." },
    { id: 6, name: "Who Were the Crews Aboard WW2 US Subs", description: "Information about submarine crews, their roles, and backgrounds." }
  ];

  const faqs = [
    {
      id: 1,
      question: "Is the USS Pampanito (SS-383) really a submarine?",
      answer: "That is actually a valid question. Modern submarines, even the non-nuclear boats of other navies, don't look anything like the Pampanito from the outside. They are sleek black shapes and are mostly underwater. Only a little of the hull shows along with the upright sail. Unlike the Pampanito, they have no guns. But, yes, this is what was described as a submarine in World War 2.",
      category_id: 1,
      category_name: "Hull and Compartments"
    },
    {
      id: 2,
      question: "What do you mean when you say this is what we called a submarine?",
      answer: "This is as far as the technology could go in the early 1940s. However, these boats, and all others of that era, technically, were just submersibles. They were surface ships that could submerge for relatively brief periods (usually 16 hours or so), could fight submerged and then could come back to the surface. The first true submarine was the USS Nautilus (SSN-571) which was commissioned in 1954.",
      category_id: 1,
      category_name: "Hull and Compartments"
    },
    {
      id: 9,
      question: "Pampanito is much bigger than I expected. I thought submarines were crowded.",
      answer: "The boat is long but narrow. It is almost 312 feet long. However, the pressure hull, where the crew lived and worked, is only 16 feet in diameter and around 280 feet long. The bottom level is all machinery and much more equipment is on the main level such as the tops of the engines and generators, fresh water stills, torpedo tubes, reload torpedoes and the boat's controls. As a result, there isn't much room left for the crew. The submarine looks much bigger from the outside than it does from the inside, particularly with 80 crew members on board.",
      category_id: 1,
      category_name: "Hull and Compartments"
    },
    {
      id: 10,
      question: "Where is the pressure hull? Can I see it from the pier?",
      answer: "The pressure hull or 'people pipe' is below the main deck and between the sets of ballast and fuel tanks on both sides of the boat. Most of the pressure hull is hidden by the tanks on both sides and the main deck above.",
      category_id: 1,
      category_name: "Hull and Compartments"
    },
    {
      id: 11,
      question: "Where do the torpedoes come out?",
      answer: "The outer doors for the forward tubes are all the way forward and just below the first row of limber holes. It is a rectangular structure that moves in toward the boat when opened. The same is true of the after tubes although there are no limber holes near the after outer doors. The outer doors for the top tubes, both forward and aft are visible on the Pampanito.",
      category_id: 1,
      category_name: "Hull and Compartments"
    },
    {
      id: 3,
      question: "How different are modern submarines?",
      answer: "Modern submarines are vastly different. With nuclear power, they can operate underwater for months at a time and do not have to surface except for crew morale and to take on supplies. They are much faster underwater than on the surface, the opposite of World War 2 submarines.",
      category_id: 2,
      category_name: "US WW2 Subs in General"
    },
    {
      id: 4,
      question: "Is a submarine a boat or a ship?",
      answer: "Submarines are called boats. This is a tradition that goes back to the early days when submarines were small and could be hoisted aboard a ship. Even though modern submarines are much larger than many surface ships, they are still called boats.",
      category_id: 2,
      category_name: "US WW2 Subs in General"
    },
    {
      id: 5,
      question: "Is the submarine a battleship?",
      answer: "No, a submarine is not a battleship. Battleships were large surface warships with heavy armor and big guns. Submarines are underwater vessels designed for stealth and surprise attacks. They serve completely different roles in naval warfare.",
      category_id: 2,
      category_name: "US WW2 Subs in General"
    },
    {
      id: 6,
      question: "What did a typical day at sea consist of?",
      answer: "A typical day consisted of two four-hour shifts on watch plus time spent for maintenance, paperwork, studying and training. The time between 08:00 and 16:00 (8 AM to 4 PM) would usually be a work day, when maintenance, etc. would be completed. Twelve hours would not be unusual for a regular workday, and it could be even longer if the sailor was working toward his submarine qualification or studying for a promotion. Sundays were a day of rest, with only eight hours of watch duty.",
      category_id: 3,
      category_name: "Life Aboard WW2 US Subs"
    },
    {
      id: 12,
      question: "What did a typical day in port consist of?",
      answer: "The typical workday would be from 08:00 to 16:00. That could be started and ended earlier, called tropical hours, so that the crew isn't working during the hottest part of the day. However, in WW2 it was often longer than eight hours as men trained or got the boat ready for the next patrol. One third of the crew would remain on board overnight for security.",
      category_id: 3,
      category_name: "Life Aboard WW2 US Subs"
    },
    {
      id: 7,
      question: "How were submarine crews selected?",
      answer: "Submarine crews were volunteers who underwent rigorous training and psychological evaluation. They needed to work well in confined spaces and handle the stress of underwater operations. The selection process was demanding because submarine duty required special skills and mental toughness.",
      category_id: 6,
      category_name: "Who Were the Crews Aboard WW2 US Subs"
    },
    {
      id: 8,
      question: "How deep could WW2 submarines dive?",
      answer: "Most WW2 US submarines had a test depth of around 300 feet, with a crush depth estimated at about 500-600 feet. However, some submarines exceeded these limits in emergency situations. The pressure hull had to withstand enormous water pressure at diving depths.",
      category_id: 4,
      category_name: "Operating US Subs in WW2"
    }
  ];

  try {
    switch (action) {
      case 'categories':
        return res.json(categories);

      case 'faqs':
        if (category_id) {
          const categoryFaqs = faqs.filter(faq =>
            faq.category_id === parseInt(category_id)
          );
          return res.json(categoryFaqs);
        } else {
          return res.json(faqs);
        }

      case 'search':
        if (q) {
          const searchResults = faqs.filter(faq =>
            faq.question.toLowerCase().includes(q.toLowerCase()) ||
            faq.answer.toLowerCase().includes(q.toLowerCase())
          );
          return res.json(searchResults);
        } else {
          return res.json([]);
        }

      case 'stats':
        return res.json({
          total_faqs: faqs.length,
          total_categories: categories.length,
          status: 'online'
        });

      default:
        return res.status(400).json({ error: 'Invalid action' });
    }

  } catch (error) {
    console.error('API error:', error);
    return res.status(500).json({ error: 'API error: ' + error.message });
  }
}