### Extensions and the Orbit Engine

Alkaline offers a powerful extension engine that allows Web developers to add new functionality to Alkaline as well as share their creations with other Alkaline users. Even if you don't know how to program, Alkaline users can easily add new features by installing extensions.

##### Installing Extensions

You can download additional extensions at the [Alkaline Lounge](/users/). You can install most themes by dragging an extension's folder to your `/extensions/` folder, choosing **Configuration > Extensions**, and clicking Install Extensions. Check with the documentation that came with your extension if any further configuration is required; some extensions will not work if left unconfigured.

##### Developing An Extension

*Warning: Experience with PHP and SQL is required to develop your own extension. You will only be able to create an extension as complex as your mastery of these programming languages.*

###### Naming Rubric

Type		|	Naming		|	Examples
------------|---------------|--------------------------
Class					| 	CamelCase		|	`Alkaline`, `AlkalineHelper`
Methods, Functions		|	lowerCamelCase	|	`import()`, `addTags()`
Variables				|	lowercase underscored |		`$photo`, `$photo_height`

###### Hook Reference

Hooks are specific places in Alkaline in which extension code is executed. Additional hooks can be embedded within themes themselves (see [Themes and the Canvas Engine](/guide/themes/)).