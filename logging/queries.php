<?php

if ( ! function_exists( 'zume_training_items' ) ) {
    function zume_training_items() : array {

        $training_items = [
            "1" => [
                "title" => "God Uses Ordinary People",
                "description" => "You'll see how God uses ordinary people doing simple things to make a big impact.",
                "host" => true,
                "mawl" => false,
            ],
            "2" => [
                "title" => "Simple Definition of Disciple and Church",
                "description" => "Discover the essence of being a disciple, making a disciple, and what is the church.",
                "host" => true,
                "mawl" => false,
            ],
            "3" => [
                "title" => "Spiritual Breathing is Hearing and Obeying God",
                "description" => "Being a disciple means we hear from God and we obey God.",
                "host" => true,
                "mawl" => false,
            ],
            "4" => [
                "title" => "SOAPS Bible Reading",
                "description" => "A tool for daily Bible study that helps you understand, obey, and share God’s Word.",
                "host" => true,
                "mawl" => true,
            ],
            "5" => [
                "title" => "Accountability Groups",
                "description" => "A tool for two or three people of the same gender to meet weekly and encourage each other in areas that are going well and reveal areas that need correction.",
                "host" => true,
                "mawl" => true,
            ],
            "6" => [
                "title" => "Consumer vs Producer Lifestyle",
                "description" => "You'll discover the four main ways God makes everyday followers more like Jesus.",
                "host" => true,
                "mawl" => false,
            ],
            "7" => [
                "title" => "How to Spend an Hour in Prayer",
                "description" => "See how easy it is to spend an hour in prayer.",
                "host" => true,
                "mawl" => true,
            ],
            "8" => [
                "title" => "Relational Stewardship – List of 100",
                "description" => "A tool designed to help you be a good steward of your relationships.",
                "host" => true,
                "mawl" => true,
            ],
            "9" => [
                "title" => "The Kingdom Economy",
                "description" => "Learn how God's economy is different from the world's. God invests more in those who are faithful with what they've already been given.",
                "host" => true,
                "mawl" => false,
            ],
            "10" => [
                "title" => "The Gospel and How to Share It",
                "description" => "Learn a way to share God’s Good News from the beginning of humanity all the way to the end of this age.",
                "host" => true,
                "mawl" => true,
            ],
            "11" => [
                "title" => "Baptism and How To Do It",
                "description" => "Jesus said, “Go and make disciples of all nations, BAPTIZING them in the name of the Father and of the Son and of the Holy Spirit…” Learn how to put this into practice.",
                "host" => true,
                "mawl" => true,
            ],
            "12" => [
                "title" => "Prepare Your 3-Minute Testimony",
                "description" => "Learn how to share your testimony in three minutes by sharing how Jesus has impacted your life.",
                "host" => true,
                "mawl" => true,
            ],
            "13" => [
                "title" => "Vision Casting the Greatest Blessing",
                "description" => "Learn a simple pattern of making not just one follower of Jesus but entire spiritual families who multiply for generations to come.",
                "host" => true,
                "mawl" => true,
            ],
            "14" => [
                "title" => "Duckling Discipleship – Leading Immediately",
                "description" => "Learn what ducklings have to do with disciple-making.",
                "host" => true,
                "mawl" => false,
            ],
            "15" => [
                "title" => "Eyes to See Where the Kingdom Isn’t",
                "description" => "Begin to see where God’s Kingdom isn’t. These are usually the places where God wants to work the most.",
                "host" => true,
                "mawl" => false,
            ],
            "16" => [
                "title" => "The Lord’s Supper and How To Lead It",
                "description" => "It’s a simple way to celebrate our intimate connection and ongoing relationship with Jesus. Learn a simple way to celebrate.",
                "host" => true,
                "mawl" => true,
            ],
            "17" => [
                "title" => "Prayer Walking and How To Do It",
                "description" => "It’s a simple way to obey God’s command to pray for others. And it's just what it sounds like — praying to God while walking around!",
                "host" => true,
                "mawl" => true,
            ],
            "18" => [
                "title" => "A Person of Peace and How To Find One",
                "description" => "Learn who a person of peace might be and how to know when you've found one.",
                "host" => true,
                "mawl" => false,
            ],
            "19" => [
                "title" => "The BLESS Prayer Pattern",
                "description" => "Practice a simple mnemonic to remind you of ways to pray for others.",
                "host" => true,
                "mawl" => true,
            ],
            "20" => [
                "title" => "Faithfulness is Better Than Knowledge",
                "description" => "It's important what disciples know — but it's much more important what they DO with what they know.",
                "host" => true,
                "mawl" => false,
            ],
            "21" => [
                "title" => "3/3 Group Meeting Pattern",
                "description" => "A 3/3 Group is a way for followers of Jesus to meet, pray, learn, grow, fellowship and practice obeying and sharing what they've learned. In this way, a 3/3 Group is not just a small group but a Simple Church.",
                "host" => true,
                "mawl" => true,
            ],
            "22" => [
                "title" => "Training Cycle for Maturing Disciples",
                "description" => "Learn the training cycle and consider how it applies to disciple making.",
                "host" => true,
                "mawl" => true,
            ],
            "23" => [
                "title" => "Leadership Cells",
                "description" => "A Leadership Cell is a way someone who feels called to lead can develop their leadership by practicing serving.",
                "host" => true,
                "mawl" => false,
            ],
            "24" => [
                "title" => "Expect Non-Sequential Growth",
                "description" => "See how disciple making doesn't have to be linear. Multiple things can happen at the same time.",
                "host" => true,
                "mawl" => false,
            ],
            "25" => [
                "title" => "Pace of Multiplication Matters",
                "description" => "Multiplying matters and multiplying quickly matters even more. See why pace matters.",
                "host" => true,
                "mawl" => false,
            ],
            "26" => [
                "title" => "Always Part of Two Churches",
                "description" => "Learn how to obey Jesus' commands by going AND staying.",
                "host" => true,
                "mawl" => true,
            ],
            "27" => [
                "title" => "Three-Month Plan",
                "description" => "Create and share your plan for how you will implement the Zúme tools over the next three months.",
                "host" => true,
                "mawl" => false,
            ],
            "28" => [
                "title" => "Coaching Checklist",
                "description" => "A powerful tool you can use to quickly assess your own strengths and vulnerabilities when it comes to making disciples who multiply.",
                "host" => true,
                "mawl" => false,
            ],
            "29" => [
                "title" => "Leadership in Networks",
                "description" => "Learn how multiplying churches stay connected and live life together as an extended, spiritual family.",
                "host" => true,
                "mawl" => false,
            ],
            "30" => [
                "title" => "Peer Mentoring Groups",
                "description" => "This is a group that consists of people who are leading and starting 3/3 Groups. It also follows a 3/3 format and is a powerful way to assess the spiritual health of God’s work in your area.",
                "host" => true,
                "mawl" => false,
            ],
            "31" => [
                "title" => "Four Fields Tool",
                "description" => "The four fields diagnostic chart is a simple tool to be used by a leadership cell to reflect on the status of current efforts and the kingdom activity around them.",
                "host" => true,
                "mawl" => true,
            ],
            "32" => [
                "title" => "Generational Mapping",
                "description" => "Generation mapping is another simple tool to help leaders in a movement understand the growth around them.",
                "host" => true,
                "mawl" => true,
            ],
        ];

        $list = [];
        foreach( $training_items as $index => $training_item ) {
            $list[$index] = [
                "title" => $training_item["title"],
                "description" => $training_item["description"],
                "host" => $training_item["host"] ? [
                    [
                        "label" => "Heard",
                        "short_label" => "H",
                        "type" => "training",
                        "subtype" => $index."_heard",
                    ],
                    [
                        "label" => "Obeyed",
                        "short_label" => "O",
                        "type" => "training",
                        "subtype" => $index."_obeyed",
                    ],
                    [
                        "label" => "Shared",
                        "short_label" => "S",
                        "type" => "training",
                        "subtype" => $index."_shared",
                    ],
                    [
                        "label" => "Trained",
                        "short_label" => "T",
                        "type" => "training",
                        "subtype" => $index."_trained",
                    ],
                ] : [],
                "mawl" => $training_item["mawl"] ? [
                    [
                        "label" => "Modeling",
                        "short_label" => "M",
                        "type" => "coaching",
                        "subtype" => $index."_modeling",
                    ],
                    [
                        "label" => "Assisting",
                        "short_label" => "A",
                        "type" => "coaching",
                        "subtype" => $index."_assisting",
                    ],
                    [
                        "label" => "Watching",
                        "short_label" => "W",
                        "type" => "coaching",
                        "subtype" => $index."_watching",
                    ],
                    [
                        "label" => "Launching",
                        "short_label" => "L",
                        "type" => "coaching",
                        "subtype" => $index."_launching",
                    ],
                ] : [],
            ];
        }

        return $list;
    }
}

if ( ! function_exists( 'zume_funnel_stages' ) ) {
    function zume_funnel_stages() : array {
        return [
            0 => [
                'label' => 'Anonymous',
                'short_label' => 'Anonymous',
                'description' => 'Anonymous visitors to the website.',
                'stage' => 0
            ],
            1 => [
                'label' => 'Registrant',
                'short_label' => 'Registered',
                'description' => 'Trainee who has registered for the training.',
                'stage' => 1
            ],
            2 => [
                'label' => 'Active Training Trainee',
                'short_label' => 'Active Training',
                'description' => 'Trainee who is in active training.',
                'stage' => 2
            ],
            3 => [
                'label' => 'Post-Training Trainee',
                'short_label' => 'Post-Training',
                'description' => 'Trainee who has completed training.',
                'stage' => 3
            ],
            4 => [
                'label' => '(S1) Partial Practitioner',
                'short_label' => 'Partial Practitioner',
                'description' => 'Practitioner still coaching through MAWL checklist.',
                'stage' => 4
            ],
            5 => [
                'label' => '(S2) Completed Practitioner',
                'short_label' => 'Practitioner',
                'description' => 'Practitioner who has completed the MAWL checklist but is not multiplying.',
                'stage' => 5
            ],
            6 => [
                'label' => '(S3) Multiplying Practitioner',
                'short_label' => 'Multiplying Practitioner',
                'description' => 'Practitioner who is seeing generational fruit.',
                'stage' => 6
            ],

        ];
    }
}
