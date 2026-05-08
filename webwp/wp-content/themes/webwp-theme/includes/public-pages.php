<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'webwp_page_url' ) ) {
    function webwp_page_url( $slug, $fallback = '#' ) {
        $page = get_page_by_path( $slug );
        if ( $page instanceof WP_Post ) {
            return get_permalink( $page );
        }

        return $fallback === '#'
            ? home_url( '/' . trim( $slug, '/' ) . '/' )
            : $fallback;
    }
}

if ( ! function_exists( 'webwp_sample_courses' ) ) {
    function webwp_sample_courses() {
        return [
            [
                'title'      => 'UI/UX Design Foundations',
                'category'   => 'Design',
                'instructor' => 'Lina Thomas',
                'price'      => '$149',
                'rating'     => '4.9',
                'lessons'    => '24 lessons',
                'duration'   => '8h 20m',
                'image'      => webwp_img( 'feat-ui.png' ),
            ],
            [
                'title'      => 'Remote Teaching Toolkit',
                'category'   => 'Teaching',
                'instructor' => 'Adam Levin',
                'price'      => '$189',
                'rating'     => '4.8',
                'lessons'    => '18 lessons',
                'duration'   => '6h 10m',
                'image'      => webwp_img( 'feat-tools.png' ),
            ],
            [
                'title'      => 'Assessment Design for Online Class',
                'category'   => 'Assessment',
                'instructor' => 'Gloria Rose',
                'price'      => '$129',
                'rating'     => '4.7',
                'lessons'    => '16 lessons',
                'duration'   => '5h 45m',
                'image'      => webwp_img( 'feat-quiz.png' ),
            ],
            [
                'title'      => 'Classroom Management Masterclass',
                'category'   => 'Operations',
                'instructor' => 'Patricia Mendoza',
                'price'      => '$219',
                'rating'     => '5.0',
                'lessons'    => '28 lessons',
                'duration'   => '9h 30m',
                'image'      => webwp_img( 'feat-gradebook.png' ),
            ],
            [
                'title'      => 'One-on-One Coaching Sessions',
                'category'   => 'Mentoring',
                'instructor' => 'Trevor Clark',
                'price'      => '$99',
                'rating'     => '4.6',
                'lessons'    => '10 lessons',
                'duration'   => '3h 20m',
                'image'      => webwp_img( 'feat-121.png' ),
            ],
            [
                'title'      => 'Interactive Course Launch Sprint',
                'category'   => 'Launch',
                'instructor' => 'Nancy White',
                'price'      => '$249',
                'rating'     => '4.9',
                'lessons'    => '30 lessons',
                'duration'   => '11h 00m',
                'image'      => webwp_img( 'teacher-lesson.png' ),
            ],
        ];
    }
}

if ( ! function_exists( 'webwp_membership_plans' ) ) {
    function webwp_membership_plans() {
        return [
            [
                'name'     => 'Starter',
                'price'    => '$0',
                'period'   => '/month',
                'featured' => false,
                'features' => [
                    '1 classroom space',
                    'Up to 25 students',
                    'Assignments and quizzes',
                    'Community support',
                ],
            ],
            [
                'name'     => 'Pro Teacher',
                'price'    => '$29',
                'period'   => '/month',
                'featured' => true,
                'features' => [
                    'Unlimited classrooms',
                    'Attendance and gradebook',
                    'Private discussion rooms',
                    'Premium analytics',
                ],
            ],
            [
                'name'     => 'Institution',
                'price'    => '$99',
                'period'   => '/month',
                'featured' => false,
                'features' => [
                    'Multi-instructor access',
                    'Advanced reporting',
                    'Priority onboarding',
                    'Billing and contracts',
                ],
            ],
        ];
    }
}

if ( ! function_exists( 'webwp_fallback_articles' ) ) {
    function webwp_fallback_articles() {
        return [
            [
                'title'   => 'Why Swift UI Should Be on the Radar of Every Mobile Developer',
                'excerpt' => 'TOTC explores how product teams can translate modern teaching patterns into polished mobile experiences.',
                'image'   => webwp_img( 'tools-hero.png' ),
                'tag'     => 'Design',
                'author'  => 'Lina Thomas',
            ],
            [
                'title'   => 'How to Launch a Better Hybrid Classroom Experience',
                'excerpt' => 'A practical playbook for combining live meetings, assignments, and async teaching inside one workflow.',
                'image'   => webwp_img( 'avatar-3.png' ),
                'tag'     => 'Teaching',
                'author'  => 'Gloria Rose',
            ],
            [
                'title'   => 'Designing Course Communities That Actually Stick',
                'excerpt' => 'Community mechanics matter. Here is how to build retention loops into your course platform.',
                'image'   => webwp_img( 'avatar-4.png' ),
                'tag'     => 'Community',
                'author'  => 'Adam Levin',
            ],
            [
                'title'   => 'The New Checklist for Remote Assessment Quality',
                'excerpt' => 'Assessment design needs structure, rhythm, and clearer student feedback paths than most teams expect.',
                'image'   => webwp_img( 'avatar-1.png' ),
                'tag'     => 'Assessment',
                'author'  => 'Nancy White',
            ],
        ];
    }
}
