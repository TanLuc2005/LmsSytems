-- Insert messages
INSERT INTO messages (sender_id, recipient_id, subject, content, sent_at)
VALUES
(1, 2, 'Welcome to the LMS!', 'Hello, welcome to the system. Let us know if you have any questions.', NOW() - INTERVAL 10 DAY),
(2, 1, 'Re: Welcome', 'Thank you! I am excited to use the platform.', NOW() - INTERVAL 9 DAY),
(1, 3, 'Assignment Reminder', 'Don''t forget to submit your assignment by the due date.', NOW() - INTERVAL 8 DAY),
(3, 1, 'Question about Assignment', 'Can you clarify the requirements for the assignment?', NOW() - INTERVAL 7 DAY),
(2, 3, 'Group Project', 'Would you like to team up for the group project?', NOW() - INTERVAL 6 DAY),
(3, 2, 'Re: Group Project', 'Yes, let''s work together!', NOW() - INTERVAL 5 DAY),
(1, 2, 'Forum Update', 'A new topic has been posted in the forum.', NOW() - INTERVAL 4 DAY),
(2, 1, 'Thanks for the update', 'I will check the new forum topic.', NOW() - INTERVAL 3 DAY),
(3, 1, 'Submission Confirmation', 'I have submitted my assignment.', NOW() - INTERVAL 2 DAY),
(1, 3, 'Grading Complete', 'Your assignment has been graded.', NOW() - INTERVAL 1 DAY);

-- Insert forum categories, topics, and posts
INSERT INTO forum_categories (course_id, name, description)
VALUES
(1, 'General Discussion', 'Talk about anything related to the course.'),
(1, 'Assignments', 'Discuss assignments and requirements.'),
(2, 'Exams', 'Exam preparation and tips.');

INSERT INTO forum_topics (category_id, title, created_by)
VALUES
(1, 'Welcome to the forum!', 1),
(1, 'Introduce Yourself', 2),
(2, 'Assignment 1 Questions', 3),
(3, 'Midterm Exam Tips', 2);

INSERT INTO forum_posts (topic_id, user_id, content)
VALUES
(1, 1, 'Hello everyone! Feel free to ask any questions here.'),
(1, 2, 'Hi! I am new to this course.'),
(2, 3, 'My name is Jane, nice to meet you all.'),
(3, 2, 'Does anyone need help with Assignment 1?'),
(3, 1, 'Yes, I am confused about the requirements.'),
(4, 2, 'Here are some tips for the midterm exam: review all lecture notes and practice problems.');
