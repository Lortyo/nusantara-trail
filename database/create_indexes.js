// MongoDB Index Creation Script
// Run this in MongoDB shell or MongoDB Compass
// Usage: mongosh nusantara_trail < create_indexes.js

// Use the database
use nusantara_trail;

print("\n=== Creating Indexes for Nusantara Trail Database ===\n");

// Users - Email must be unique
print("Creating unique index on users.email...");
db.users.createIndex({ "email": 1 }, { unique: true });
print("✓ Done\n");

// Registrations - Frequently queried by user_id and event_id
print("Creating compound index on registrations (user_id, event_id)...");
db.registrations.createIndex({ "user_id": 1, "event_id": 1 });
print("✓ Done\n");

// Registrations - For expired cleanup task
print("Creating compound index on registrations (status, expiresAt)...");
db.registrations.createIndex({ "status": 1, "expiresAt": 1 });
print("✓ Done\n");

// Payments - orderId must be unique and frequently searched
print("Creating unique index on payments.orderId...");
db.payments.createIndex({ "orderId": 1 }, { unique: true });
print("✓ Done\n");

// Payments - Query by user and status
print("Creating compound index on payments (user_id, status)...");
db.payments.createIndex({ "user_id": 1, "status": 1 });
print("✓ Done\n");

// Payments - For registration lookups
print("Creating index on payments.registration_id...");
db.payments.createIndex({ "registration_id": 1 });
print("✓ Done\n");

// Payments - For category change lookups
print("Creating index on payments.change_id...");
db.payments.createIndex({ "change_id": 1 });
print("✓ Done\n");

// RaceEvents - Filtered by status and date in event listing
print("Creating compound index on race_events (status, eventDate)...");
db.race_events.createIndex({ "status": 1, "eventDate": 1 });
print("✓ Done\n");

// RaceCategories - Frequently joined by event_id
print("Creating index on race_categories.event_id...");
db.race_categories.createIndex({ "event_id": 1 });
print("✓ Done\n");

// Qualifications - Query by user, category, and status
print("Creating compound index on qualifications (user_id, category_id, status)...");
db.qualifications.createIndex({ "user_id": 1, "category_id": 1, "status": 1 });
print("✓ Done\n");

// Tickets - qrToken must be unique for check-in
print("Creating unique index on tickets.qrToken...");
db.tickets.createIndex({ "qrToken": 1 }, { unique: true });
print("✓ Done\n");

// Tickets - Lookup by registration_id
print("Creating index on tickets.registration_id...");
db.tickets.createIndex({ "registration_id": 1 });
print("✓ Done\n");

// CategoryChanges - For expired cleanup and user queries
print("Creating compound index on category_changes (status, expiresAt)...");
db.category_changes.createIndex({ "status": 1, "expiresAt": 1 });
print("✓ Done\n");

// CategoryChanges - Query by registration
print("Creating index on category_changes.registration_id...");
db.category_changes.createIndex({ "registration_id": 1 });
print("✓ Done\n");

// EventSubscriptions - Query by user and event
print("Creating compound index on event_subscriptions (user_id, event_id)...");
db.event_subscriptions.createIndex({ "user_id": 1, "event_id": 1 });
print("✓ Done\n");

// Profiles - Lookup by user_id
print("Creating unique index on profiles.user_id...");
db.profiles.createIndex({ "user_id": 1 }, { unique: true });
print("✓ Done\n");

// Results - Lookup by registration_id
print("Creating index on results.registration_id...");
db.results.createIndex({ "registration_id": 1 });
print("✓ Done\n");

print("\n=== All indexes created successfully! ===\n");
print("\nTo verify indexes, run:");
print("  db.users.getIndexes()");
print("  db.registrations.getIndexes()");
print("  etc.\n");
