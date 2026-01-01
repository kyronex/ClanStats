import React, { useState, useEffect, useRef, useMemo, use } from "react";

function Test({ products, category, minPrice, searchTerm }) {
  const lowerSearchTerm = useMemo(() => searchTerm.toLowerCase(), [searchTerm]);

  const filteredProductsOptimised = useMemo(() => {
    return products.filter(
      (product) =>
        product.category === category &&
        product.price >= minPrice &&
        product.name.toLowerCase().includes(lowerSearchTerm)
    );
  }, [products, category, minPrice, searchTerm]);

  const sortedProducts = useMemo(() => {
    return [...filteredProducts].sort((a, b) => b.price - a.price);
  }, [filteredProductsOptimised]);

  const stats = useMemo(() => {
    return {
      totalValue: filteredProductsOptimised.reduce((sum, product) => sum + product.price, 0),
      averagePrice:
        filteredProductsOptimised.length > 0
          ? filteredProductsOptimised.reduce((sum, product) => sum + product.price, 0) /
            filteredProductsOptimised.length
          : 0,
      count: filteredProductsOptimised.length,
    };
  }, [filteredProductsOptimised]);

  return (
    <div>
      <h2>Produits {category}</h2>
      <p>Prix total: {stats.totalValue}€</p>
      <p>Prix moyen: {stats.averagePrice}€</p>
      <p>Nombre: {stats.count}</p>

      {sortedProducts.map((product) => (
        <div key={product.id}>
          <h3>{product.name}</h3>
          <p>{product.price}€</p>
        </div>
      ))}
    </div>
  );
}
export default Test;
