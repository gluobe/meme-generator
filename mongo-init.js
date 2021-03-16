// mongodb initiation

db = db.getSiblingDB('memegen')

db.createUser({
    user: "student",
    pwd: "Cloud247",
    roles: [ 
	{ 
		role: "root", 
		db: "admin",
	},
    ],
});

