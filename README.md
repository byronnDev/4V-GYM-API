# 🏋️‍♀️ 4vGYM API REST 🏋️‍♂️

In this delivery, we will work on the following aspects:

- 🎛️ Creation of controllers
- 💾 Persistence of information in a relational model
- 🔗 Creation of relationships between entities and their representation in the relational model (M --> 1 and N-->M)

> [!NOTE]
> 📝 This project is designed to be a REST API for a gym management system. It is important to follow the specifications closely to ensure the system functions as expected.

## 📋 Specifications

The development of a REST API for our 4vGYM is requested. This REST API will have the following specifications:

### 🏃‍♀️ /activity-types

- GET: Returns the list of Activity types. Each activity consists of an ID, name, and the number of monitors required to perform it.

### 👨‍🏫 /monitors

- GET: Returns the list of monitors ID, Name, Email, Phone, and Photo.
- POST: Allows creating new monitors and returns the JSON with the information of the new monitor.
- PUT: Allows editing existing monitors.
- DELETE: Allows deleting monitors.

> [!TIP]
> 💡 When creating or editing monitors, ensure that all required fields are filled out correctly. This will prevent errors and ensure that the system functions smoothly.

### 📅 /activities

- GET: Returns the list of Activities, with all the information about the types, included monitors, and the date. Allows searching by a date parameter that will have a format of dd-MM-yyyy.
- POST: Allows creating new activities and returns the information of the new activity. It must be validated that the new activity has the required monitors according to the activity type. The date and duration are not free fields. Only 90-minute classes starting at 09:00, 13:30, and 17:30 are allowed.
- PUT: Allows editing existing activities.
- DELETE: Allows deleting activities.

> [!IMPORTANT]
> ⚠️ The entire API must have validation for POST fields. This is crucial to maintain data integrity and prevent errors.

## 🗄️ Database

It is assumed that the supporting database contains the following:

- 📋 Monitors table.
- 📋 Activity Types table.
- 📋 Activities table. (FK on Activity Types)
- 📋 Activities-Monitors table (N-M)

Everything produced will be in English, class names, tables, JSON modeling, etc.

> [!WARNING]
> 🚨 Be careful when manipulating the database directly. Incorrect changes can lead to data loss or system malfunction.

> [!CAUTION]
> 🛠️ Remember to follow best practices for data modeling and database design. This will help ensure the system is scalable, efficient, and easy to maintain.

## Author ✒️
Mikel Echeverria