+++
title = "Array, Table, Struct and Set"
weight = 9
+++

Phel has three mutable datastructures. An array is an indexed sequential datastructure, a table is an associative datastructure and a set is a unsorted sequential data structure that cannot contain twice the same value. While PHP has one single datastructure to represents arrays and hash tables, Phel splits them into individual datastructures.

An array can be created using the `@[` reader macro or the `array` function.

```phel
@["a" "b" "c"]
(array "a" "b" "c")
```

A table can be created using the `@{` reader macro or the `table` function.

```phel
@{:key1 "value1" :key2 "value2"}
(table :key1 "value1" :key2 "value2")
```

A set is can be created using the `set` function.

```phel
(set 1 2 3)
(set 1 2 3 1 2 3) # Evaluates to (set 1 2 3)
```

## Getting values

Similar to tuples, the functions `get`, `first`, `second`, `next` and `rest` can be used to get values of an array.

```phel
(get @[1 2 3] 2) # Evaluates to 3
(get @[] 2) # Evaluates to nil

(first @[1 2 3]) # Evaluates to 1
(first @[]) # Evaluates to nil

(second @[1 2 3]) # Evaluates to 2
(second @[]) # Evaluates to nil

(rest @[1 2 3]) # Evaluates to [2 3]
(rest @[]) # Evaluates to []

(next @[1 2 3]) # Evaluates to [2 3]
(next @[]) # Evaluates to nil
```

The `get` function can also be used on tables.

```phel
(get @{:a 1 :b 2} :a) # Evaluates to 1
(get @{:a 1 :b 2} :b) # Evaluates to 2
(get @{:a 1 :b 2} :c) # Evaluates to nil
```

For the function calls above Phel provides a nice shorthand. The Keyword can be used as a function to access the data.

```phel
(:a @{:a 1 :b 2}) # Evaluates to 1
(:b @{:a 1 :b 2}) # Evaluates to 2
(:c @{:a 1 :b 2}) # Evaluates to nil
```

The function `first`, `next` and `rest` can be used on sets, too. However, `next` and `rest` return an array and not a set.

```phel
(first (set 1 2 3)) # Evaluates to 1
(next (set 1 2 3)) # Evaluates to @[2 3]
(rest (set 1 2 3)) # Evaluates to @[2 3]
```

## Setting values

To set a value on an array or a table, use the `put` function. If the given index is greater than the length of the array, the array is padded with `nil`.

```phel
(let [a @[]]
  (put a 0 :hello)  # @[:hello]
  (put a 2 :world)) # @[:hello nil :world]

(let [a @{}]
  (put a :a "hello")  # @{:a "hello"}
  (put a :b "world")) # @{:a "hello" :b "world"}
```

It is not possible to set values on a set.

## Adding values

Values can be added to a set with the `push` function. This function can also be used on arrays but is described in section [Array as a Stack](#array-as-a-stack) with mode details.

```phel
(push (set 1 2) 3) # Evaluates to (set 1 2 3)
```

## Removing values

To remove a single value from an array or table the `unset` function can be used.

```phel
(unset @[:a :b :c :d] 1) # @[:a :c :d]
(unset @{:a 1 :b 2} :a) # @{:b 2}
```

To remove multiple values from arrays, the `remove` and `slice` functions can be used. The function `remove` removes the values from the data structures and returns the removed values. The `slice` function on the other hand, returns a copy of the array with only the `sliced` elements. The original data structure is left untouched.

```phel
(let [xs @[1 2 3 4]]
  (remove xs 2)) # Evaluates to @[3 4], xs is now @[1 2]

(let [xs @[1 2 3 4]]
  (remove xs 2 1)) # Evaluates to @[3], xs is now @[1 2 4]

(slice @[1 2 3 4] 2) # Evaluates to @[3 4]
(slice @[1 2 3 4] 2 1) # Evaluates to @[3]
```

It is not possible to remove values from a set.

## Count elements

To count the number of element of an array, a table or a set, the `count` function can be used.

```phel
(count @[1 2 3]) # Evaluates to 3
(count @[]) # Evaluates to 0

(count @{:a 1 :b 2}) # Evaluates to 2
(count @{}) # Evaluates to 0

(count (set 1 2 3)) # Evaluates to 3
(count (set)) # Evaluates to 0
```

## Array as a Stack

An array can also be used as a stack. Therefore, the `push`, `peek` and `pop` functions can be used. The `push` function add a new value to the stack. The `peek` function returns the last element on the stack but does not remove it. The `pop` function returns the last element on the stack and removes it.

```phel
(let [arr @[]]
  (push arr 1) # -> @[1]
  (peek arr) # Evaluates to 1, arr is unchanged
  (pop arr)) # Evaluates to 1, arr is empty @[]
```

## Set operations

A set provides very specific functions that are described in set theory. These are `union`, `intersection`, `difference` and `symmetric-difference`.

### Union

The union of a collection of sets is the set of all elements in the collection.

```phel
(union) # Evaluates to (set)
(union (set 1 2)) # Evaluates to (set 1 2)
(union (set 1 2) (set 0 3)) # Evaluates to (set 0 1 2 3)
```

### Intersection

The intersection of two sets or more is the set containing all elements shared between those sets.

```phel
(intersection (set 1 2) (set 0 3)) # Evaluates to (set)
(intersection (set 1 2) (set 0 1 2 3)) # Evaluates to (set 1 2)
```

### Difference

The difference of two sets or more is the set containing all elements in the first set that aren't in the other sets.

```phel
(difference (set 1 2) (set 0 3)) # Evaluates to (set 1 2)
(difference (set 1 2) (set 0 1 2 3)) # Evaluates to (set)
(difference (set 0 1 2 3) (set 1 2)) # Evaluates to (set 0 3)
```

### Symmetric difference

The symmetric difference of two sets or more is the set of elements which are in either of the sets and not in their intersection.

```phel
(symmetric-difference (set 1 2) (set 0 3)) # Evaluates to (set 0 1 2 3)
(symmetric-difference (set 1 2) (set 0 1 2 3)) # Evaluates to (set 0 3)
```

## Struct

A struct is a special kind of table. It only supports a predefined number of keys and is associated to a global name. The struct not only defines itself but also a predicate function.

```phel
(defstruct my-struct [a b c]) # Defines the struct
(let [x (my-struct 1 2 3)] # Create a new struct
  (my-struct? x) # Evaluates to true
  (get x :a) # Evaluates to 1
  (put x :a 12) # Evaluates to (my-struct 12 2 3)
```
