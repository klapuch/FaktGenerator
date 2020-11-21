"""Primitive tuple ops for *variable-length* tuples.

Note: Varying-length tuples are represented as boxed Python tuple
objects, i.e. tuple_rprimitive (RPrimitive), not RTuple.
"""

from mypyc.ir.ops import ERR_MAGIC
from mypyc.ir.rtypes import (
    tuple_rprimitive, int_rprimitive, list_rprimitive, object_rprimitive, c_pyssize_t_rprimitive
)
from mypyc.primitives.registry import (
    c_method_op, c_function_op, c_custom_op
)


# tuple[index] (for an int index)
tuple_get_item_op = c_method_op(
    name='__getitem__',
    arg_types=[tuple_rprimitive, int_rprimitive],
    return_type=object_rprimitive,
    c_function_name='CPySequenceTuple_GetItem',
    error_kind=ERR_MAGIC)

# Construct a boxed tuple from items: (item1, item2, ...)
new_tuple_op = c_custom_op(
    arg_types=[c_pyssize_t_rprimitive],
    return_type=tuple_rprimitive,
    c_function_name='PyTuple_Pack',
    error_kind=ERR_MAGIC,
    var_arg_type=object_rprimitive)

# Construct tuple from a list.
list_tuple_op = c_function_op(
    name='builtins.tuple',
    arg_types=[list_rprimitive],
    return_type=tuple_rprimitive,
    c_function_name='PyList_AsTuple',
    error_kind=ERR_MAGIC,
    priority=2)

# Construct tuple from an arbitrary (iterable) object.
c_function_op(
    name='builtins.tuple',
    arg_types=[object_rprimitive],
    return_type=tuple_rprimitive,
    c_function_name='PySequence_Tuple',
    error_kind=ERR_MAGIC)
