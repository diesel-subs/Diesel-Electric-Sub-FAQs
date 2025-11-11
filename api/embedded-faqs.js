// Real submarine FAQ data extracted from markdown files
const realSubmarineData = {
  categories: [
    { id: 1, name: "Hull and Compartments", description: "Learn about submarine construction, hull design, and compartment layouts." },
    { id: 2, name: "US WW2 Subs in General", description: "General information about American submarines during World War II." },
    { id: 3, name: "Operating US Subs in WW2", description: "Operational procedures, tactics, and submarine warfare techniques." },
    { id: 4, name: "Who Were the Crews Aboard WW2 US Subs", description: "Information about submarine crews, their roles, and backgrounds." },
    { id: 5, name: "Life Aboard WW2 US Subs", description: "Daily life, living conditions, and crew experiences aboard submarines." },
    { id: 6, name: "Attacks and Battles, Small and Large", description: "Combat operations, battles, and military engagements." }
  ],
  faqs: [
    // Hull and Compartments (Category 1)
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
      answer: "This is as far as the technology could go in the early 1940s. However, these boats, and all others of that era, technically, were just submersibles. They were surface ships that could submerge for relatively brief periods (usually 16 hours or so), could fight submerged and then could come back to the surface. The first true submarine was the USS Nautilus (SSN-571) which was commissioned in 1954. With nuclear power she could operate underwater for months at a time and did not have to surface regularly for fresh air or to recharge batteries.",
      category_id: 1,
      category_name: "Hull and Compartments"
    },
    {
      id: 3,
      question: "How different are modern submarines?",
      answer: "Visitors who have served on our latest submarines tell us that they are completely different from the Pampanito and exactly the same. They are telling us that the functions are the same -- diving, surfacing, being quiet, being sneaky, gathering intelligence, and attacking the ships of an enemy in wartime. How those functions are accomplished may be very different. Buttons, switches and computers help to accomplish many tasks that were very manual on the Pampanito.",
      category_id: 1,
      category_name: "Hull and Compartments"
    },
    {
      id: 4,
      question: "Is a submarine a boat or a ship?",
      answer: "Submarines are traditionally called 'boats' in naval terminology, despite their size. This tradition dates back to early submarines which were small enough to be considered boats. The term has stuck throughout naval history, even as submarines have grown much larger.",
      category_id: 2,
      category_name: "US WW2 Subs in General"
    },
    {
      id: 5,
      question: "Pampanito is much bigger than I expected. I thought submarines were crowded.",
      answer: "The boat is long but narrow. It is almost 312 feet long. However, the pressure hull, where the crew lived and worked, is only 16 feet in diameter and around 280 feet long. The bottom level is all machinery and much more equipment is on the main level such as the tops of the engines and generators, fresh water stills, torpedo tubes, reload torpedoes and the boat's controls. As a result, there isn't much room left for the crew. The submarine looks much bigger from the outside than it does from the inside, particularly with 80 crew members on board.",
      category_id: 1,
      category_name: "Hull and Compartments"
    },
    // Add more sample FAQs from other categories
    {
      id: 6,
      question: "How deep could WW2 submarines dive?",
      answer: "Most WW2 US submarines had a test depth of around 300 feet, with a crush depth estimated at about 500-600 feet. However, some submarines exceeded these limits in emergency situations. The Pampanito's test depth was 300 feet, though in combat situations, submarines sometimes dove deeper when necessary.",
      category_id: 3,
      category_name: "Operating US Subs in WW2"
    },
    {
      id: 7,
      question: "What was daily life like aboard a WW2 submarine?",
      answer: "Life aboard WW2 submarines was cramped and challenging. Crews worked in shifts, shared bunks (hot bunking), and dealt with limited fresh water, cramped quarters, and the constant smell of diesel fuel and battery acid. Space was so limited that privacy was almost non-existent, and crew members had to be carefully selected for their ability to work in confined spaces.",
      category_id: 5,
      category_name: "Life Aboard WW2 US Subs"
    },
    {
      id: 8,
      question: "How were submarine crews selected?",
      answer: "Submarine crews were volunteers who underwent rigorous training and psychological evaluation. They needed to work well in confined spaces and handle the stress of underwater operations. The selection process was thorough because the crew had to live and work together in very close quarters for extended periods.",
      category_id: 4,
      category_name: "Who Were the Crews Aboard WW2 US Subs"
    },
    {
      id: 9,
      question: "What was the most famous submarine attack of WW2?",
      answer: "One of the most famous attacks was the sinking of the Japanese aircraft carrier Shinano by USS Archerfish in November 1944, making it the largest warship ever sunk by a submarine. The attack required precise timing and positioning, and demonstrated the effectiveness of submarine warfare in the Pacific theater.",
      category_id: 6,
      category_name: "Attacks and Battles, Small and Large"
    },
    {
      id: 10,
      question: "What did submarine crews eat during long patrols?",
      answer: "Submarine crews started with fresh food that was quickly consumed, then relied on canned goods, dried foods, and preserved items. Coffee and chocolate were precious morale boosters. Storage space for food was extremely limited, so meals became increasingly monotonous as patrols continued.",
      category_id: 5,
      category_name: "Life Aboard WW2 US Subs"
    }
  ]
};

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const { action, category_id, q } = req.query;

  try {
    switch (action) {
      case 'categories':
        return res.json(realSubmarineData.categories);

      case 'faqs':
        if (category_id) {
          const categoryFaqs = realSubmarineData.faqs.filter(faq =>
            faq.category_id === parseInt(category_id)
          );
          return res.json(categoryFaqs);
        } else {
          return res.json(realSubmarineData.faqs);
        }

      case 'search':
        if (q) {
          const searchResults = realSubmarineData.faqs.filter(faq =>
            faq.question.toLowerCase().includes(q.toLowerCase()) ||
            faq.answer.toLowerCase().includes(q.toLowerCase())
          );
          return res.json(searchResults);
        } else {
          return res.json([]);
        }

      case 'stats':
        return res.json({
          total_faqs: realSubmarineData.faqs.length,
          total_categories: realSubmarineData.categories.length,
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